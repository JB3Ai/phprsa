<?php
/**
 * PHP RSA ID Validator Test Suite
 * 
 * Comprehensive unit tests for the RsaIdValidator class
 * 
 * @package     PhpRsaIdValidator
 * @author      Lwando Nkenjana
 * @copyright   2024 NITS Tech Systems
 * @license     MIT
 */

declare(strict_types=1);

namespace PhpRsaIdValidator\Tests;

use PhpRsaIdValidator\RsaIdValidator;
use PHPUnit\Framework\TestCase;

/**
 * Test class for RsaIdValidator
 */
class RsaIdValidatorTest extends TestCase
{
    private RsaIdValidator $validator;

    protected function setUp(): void
    {
        $this->validator = new RsaIdValidator();
    }

    /**
     * Test valid ID numbers
     * 
     * @dataProvider validIdProvider
     */
    public function testValidIds(string $id, string $expectedDob, string $expectedGender, string $expectedCitizenship): void
    {
        $result = $this->validator->validate($id);
        
        $this->assertTrue($result['valid']);
        $this->assertEquals($expectedDob, $result['date_of_birth']);
        $this->assertEquals($expectedGender, $result['gender']);
        $this->assertEquals($expectedCitizenship, $result['citizenship']);
    }

    /**
     * Test invalid ID numbers
     * 
     * @dataProvider invalidIdProvider
     */
    public function testInvalidIds(string $id, string $expectedError): void
    {
        $result = $this->validator->validate($id);
        
        $this->assertFalse($result['valid']);
        $this->assertStringContainsString($expectedError, $result['error']);
    }

    /**
     * Test invalid input types
     */
    public function testInvalidInputType(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->validator->validate([]);
    }

    public function validIdProvider(): array
    {
        return [
            // Format: [ID, Expected DOB, Expected Gender, Expected Citizenship]
            ['9001014800081', '1990-01-01', 'Male', 'SA Citizen'],
            ['0801014800086', '2008-01-01', 'Male', 'SA Citizen'],
            ['8508304500082', '1985-08-30', 'Female', 'SA Citizen'],
        ];
    }

    public function invalidIdProvider(): array
    {
        return [
            // Format: [ID, Expected Error]
            ['123', 'must be exactly 13 digits'],
            ['9001314800081', 'Invalid birth date'], // Invalid date
            ['9001014800082', 'Luhn validation'], // Wrong check digit
            ['ABCDEFGHIJKLM', 'must be exactly 13 digits'], // Non-numeric
        ];
    }
}