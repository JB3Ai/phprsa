<?php
/**
 * PHP RSA ID Validator
 * 
 * A professional validation library for South African ID numbers
 * Compliant with South African Department of Home Affairs specifications
 * 
 * @package     PhpRsaIdValidator
 * @author      Lwando Nkenjana
 * @copyright   2024 NITS Tech Systems
 * @license     MIT
 * @version     1.0.0
 * @link        https://github.com/yourusername/phprsa-id-validator
 */

declare(strict_types=1);

namespace PhpRsaIdValidator;

use DateTime;
use InvalidArgumentException;

/**
 * RSA ID Number Validator
 * 
 * Validates South African ID numbers according to official specifications:
 * - Format: YYMMDDSSSSCAZ
 * - Luhn algorithm verification
 * - Date validation with century determination
 * - Gender and citizenship extraction
 * 
 * @final This class should not be extended
 */
final class RsaIdValidator
{
    private const ID_LENGTH = 13;
    private const PATTERN = '/^[0-9]{13}$/';
    private const GENDER_THRESHOLD = 5000;
    
    /**
     * Validates a South African ID number
     * 
     * @param string $id The ID number to validate
     * @return array Validation result with extracted information
     * @throws InvalidArgumentException If input is not a string
     * 
     * @example
     * $validator = new RsaIdValidator();
     * $result = $validator->validate('9001014800081');
     * 
     * Returns:
     * [
     *     'valid' => true,
     *     'date_of_birth' => '1990-01-01',
     *     'gender' => 'Male',
     *     'citizenship' => 'SA Citizen',
     *     'check_digit' => '1'
     * ]
     */
    public function validate(string $id): array
    {
        // Input validation
        if (!is_string($id)) {
            throw new InvalidArgumentException('ID must be a string');
        }

        $id = trim($id);
        $id = preg_replace('/\s+/', '', $id); // Remove any whitespace

        // Basic format validation
        if (!$this->validateFormat($id)) {
            return [
                'valid' => false, 
                'error' => 'Invalid ID format: must be exactly 13 digits'
            ];
        }

        try {
            // Extract components from ID number
            $components = $this->extractComponents($id);
            
            // Validate birth date
            if (!$this->validateBirthDate($components['yy'], $components['mm'], $components['dd'])) {
                return [
                    'valid' => false, 
                    'error' => 'Invalid birth date in ID'
                ];
            }

            // Validate check digit using Luhn algorithm
            if (!$this->validateLuhn($id)) {
                return [
                    'valid' => false, 
                    'error' => 'Invalid check digit (Luhn validation failed)'
                ];
            }

            // Return successful validation with extracted data
            return $this->buildSuccessResponse($components, $id);

        } catch (\Exception $e) {
            return [
                'valid' => false, 
                'error' => 'Validation error: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Validates ID number format
     * 
     * @param string $id The ID number to validate
     * @return bool True if format is valid
     */
    private function validateFormat(string $id): bool
    {
        return preg_match(self::PATTERN, $id) === 1 && strlen($id) === self::ID_LENGTH;
    }

    /**
     * Extracts and validates components from ID number
     * 
     * @param string $id The ID number
     * @return array Extracted components
     */
    private function extractComponents(string $id): array
    {
        return [
            'yy' => substr($id, 0, 2),
            'mm' => substr($id, 2, 2),
            'dd' => substr($id, 4, 2),
            'gender_digits' => substr($id, 6, 4),
            'citizenship_digit' => substr($id, 10, 1),
            'check_digit' => substr($id, 12, 1)
        ];
    }

    /**
     * Validates birth date with century determination
     * 
     * @param string $yy Two-digit year
     * @param string $mm Two-digit month
     * @param string $dd Two-digit day
     * @return bool True if date is valid
     */
    private function validateBirthDate(string $yy, string $mm, string $dd): bool
    {
        $fullYear = $this->determineCentury($yy, $mm, $dd);
        return checkdate((int)$mm, (int)$dd, $fullYear);
    }

    /**
     * Determines the correct century for birth year
     * Uses age-based logic to handle century crossover
     * 
     * @param string $yy Two-digit year
     * @param string $mm Two-digit month
     * @param string $dd Two-digit day
     * @return int Full four-digit year
     */
    private function determineCentury(string $yy, string $mm, string $dd): int
    {
        $currentYear = (int)date('Y');
        $currentMonth = (int)date('m');
        $currentDay = (int)date('d');
        
        $candidateYear20xx = (int)('20' . $yy);
        $candidateYear19xx = (int)('19' . $yy);
        
        // Calculate age for both century possibilities
        $age20xx = $this->calculateAge($candidateYear20xx, (int)$mm, (int)$dd);
        $age19xx = $this->calculateAge($candidateYear19xx, (int)$mm, (int)$dd);
        
        // Prefer the century that gives a reasonable age (0-122 years)
        // and where the birth date has already occurred
        if ($age20xx >= 0 && $age20xx <= 122) {
            return $candidateYear20xx;
        }
        
        if ($age19xx >= 0 && $age19xx <= 122) {
            return $candidateYear19xx;
        }
        
        // Default to 19xx if both are problematic (should be caught by date validation)
        return $candidateYear19xx;
    }

    /**
     * Calculates age based on birth date
     * 
     * @param int $year Birth year
     * @param int $month Birth month
     * @param int $day Birth day
     * @return int Age in years
     */
    private function calculateAge(int $year, int $month, int $day): int
    {
        $today = new DateTime();
        $birthDate = DateTime::createFromFormat('Y-m-d', sprintf('%04d-%02d-%02d', $year, $month, $day));
        
        if (!$birthDate) {
            return -1;
        }
        
        $age = $today->diff($birthDate)->y;
        
        // If birth date hasn't occurred yet this year, subtract 1
        $currentMonth = (int)$today->format('m');
        $currentDay = (int)$today->format('d');
        
        if ($currentMonth < $month || ($currentMonth === $month && $currentDay < $day)) {
            $age--;
        }
        
        return $age;
    }

    /**
     * Validates ID number using Luhn algorithm
     * 
     * @param string $id The ID number to validate
     * @return bool True if Luhn check passes
     */
    private function validateLuhn(string $id): bool
    {
        $sum = 0;
        $length = strlen($id);
        
        for ($i = 0; $i < $length - 1; $i++) {
            $digit = (int)$id[$i];
            
            // Double every second digit from the right
            if ($i % 2 === 0) {
                $digit *= 2;
                if ($digit > 9) {
                    $digit -= 9;
                }
            }
            
            $sum += $digit;
        }
        
        $checkDigit = (int)$id[$length - 1];
        $calculatedCheckDigit = (10 - ($sum % 10)) % 10;
        
        return $checkDigit === $calculatedCheckDigit;
    }

    /**
     * Determines gender from ID number
     * 
     * @param string $genderDigits The gender digits from ID
     * @return string Gender description
     */
    private function determineGender(string $genderDigits): string
    {
        return ((int)$genderDigits >= self::GENDER_THRESHOLD) ? 'Male' : 'Female';
    }

    /**
     * Determines citizenship status from ID number
     * 
     * @param string $citizenshipDigit The citizenship digit from ID
     * @return string Citizenship description
     */
    private function determineCitizenship(string $citizenshipDigit): string
    {
        return ($citizenshipDigit === '0') ? 'SA Citizen' : 'Permanent Resident';
    }

    /**
     * Builds success response with extracted data
     * 
     * @param array $components Extracted ID components
     * @param string $id Original ID number
     * @return array Success response
     */
    private function buildSuccessResponse(array $components, string $id): array
    {
        $fullYear = $this->determineCentury($components['yy'], $components['mm'], $components['dd']);
        
        return [
            'valid' => true,
            'id_number' => $id,
            'date_of_birth' => sprintf('%04d-%02d-%02d', $fullYear, $components['mm'], $components['dd']),
            'gender' => $this->determineGender($components['gender_digits']),
            'citizenship' => $this->determineCitizenship($components['citizenship_digit']),
            'check_digit' => $components['check_digit'],
            'components' => [
                'birth_year' => $fullYear,
                'birth_month' => (int)$components['mm'],
                'birth_day' => (int)$components['dd'],
                'gender_code' => (int)$components['gender_digits'],
                'citizenship_code' => (int)$components['citizenship_digit']
            ]
        ];
    }
}