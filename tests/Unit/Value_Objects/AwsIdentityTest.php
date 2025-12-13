<?php
/**
 * Unit tests for Aws_Identity value object
 *
 * @package S3_Media_Sync
 */

declare( strict_types=1 );

namespace S3_Media_Sync\Tests\Unit\Value_Objects;

use S3_Media_Sync\Tests\Unit\TestCase;
use S3_Media_Sync\Value_Objects\Aws_Identity;

/**
 * Test case for Aws_Identity value object.
 *
 * @group unit
 * @group value-objects
 * @covers \S3_Media_Sync\Value_Objects\Aws_Identity
 */
class AwsIdentityTest extends TestCase {

	/**
	 * Test from_sts_response factory method.
	 */
	public function test_from_sts_response_creates_identity(): void {
		$response = array(
			'Account' => '123456789012',
			'Arn'     => 'arn:aws:iam::123456789012:user/MyUser',
			'UserId'  => 'AIDAEXAMPLEUSERID',
		);

		$identity = Aws_Identity::from_sts_response( $response );

		$this->assertSame( '123456789012', $identity->get_account_id() );
		$this->assertSame( 'arn:aws:iam::123456789012:user/MyUser', $identity->get_arn() );
		$this->assertSame( 'AIDAEXAMPLEUSERID', $identity->get_user_id() );
	}

	/**
	 * Test from_sts_response handles missing keys.
	 */
	public function test_from_sts_response_handles_missing_keys(): void {
		$response = array();

		$identity = Aws_Identity::from_sts_response( $response );

		$this->assertSame( '', $identity->get_account_id() );
		$this->assertSame( '', $identity->get_arn() );
		$this->assertSame( '', $identity->get_user_id() );
	}

	/**
	 * Test create factory method.
	 */
	public function test_create_builds_identity(): void {
		$identity = Aws_Identity::create(
			'123456789012',
			'arn:aws:iam::123456789012:role/MyRole',
			'AROAEXAMPLEROLEID:session'
		);

		$this->assertSame( '123456789012', $identity->get_account_id() );
		$this->assertSame( 'arn:aws:iam::123456789012:role/MyRole', $identity->get_arn() );
		$this->assertSame( 'AROAEXAMPLEROLEID:session', $identity->get_user_id() );
	}

	/**
	 * Test get_entity_name extracts user name.
	 */
	public function test_get_entity_name_extracts_user(): void {
		$identity = Aws_Identity::create(
			'123456789012',
			'arn:aws:iam::123456789012:user/MyUser'
		);

		$this->assertSame( 'user/MyUser', $identity->get_entity_name() );
	}

	/**
	 * Test get_entity_name extracts role name.
	 */
	public function test_get_entity_name_extracts_role(): void {
		$identity = Aws_Identity::create(
			'123456789012',
			'arn:aws:sts::123456789012:assumed-role/MyRole/session'
		);

		$this->assertSame( 'assumed-role/MyRole/session', $identity->get_entity_name() );
	}

	/**
	 * Test get_entity_name returns empty string for empty ARN.
	 */
	public function test_get_entity_name_handles_empty_arn(): void {
		$identity = Aws_Identity::create( '123456789012', '' );

		$this->assertSame( '', $identity->get_entity_name() );
	}

	/**
	 * Test is_user returns true for IAM user.
	 */
	public function test_is_user_returns_true_for_user(): void {
		$identity = Aws_Identity::create(
			'123456789012',
			'arn:aws:iam::123456789012:user/MyUser'
		);

		$this->assertTrue( $identity->is_user() );
		$this->assertFalse( $identity->is_role() );
	}

	/**
	 * Test is_role returns true for assumed role.
	 */
	public function test_is_role_returns_true_for_assumed_role(): void {
		$identity = Aws_Identity::create(
			'123456789012',
			'arn:aws:sts::123456789012:assumed-role/MyRole/session'
		);

		$this->assertTrue( $identity->is_role() );
		$this->assertFalse( $identity->is_user() );
	}

	/**
	 * Test is_role returns true for IAM role.
	 */
	public function test_is_role_returns_true_for_iam_role(): void {
		$identity = Aws_Identity::create(
			'123456789012',
			'arn:aws:iam::123456789012:role/MyRole'
		);

		$this->assertTrue( $identity->is_role() );
		$this->assertFalse( $identity->is_user() );
	}

	/**
	 * Test to_array returns expected structure.
	 */
	public function test_to_array_returns_expected_structure(): void {
		$identity = Aws_Identity::create(
			'123456789012',
			'arn:aws:iam::123456789012:user/MyUser',
			'AIDAEXAMPLEUSERID'
		);

		$array = $identity->to_array();

		$this->assertSame(
			array(
				'account_id' => '123456789012',
				'arn'        => 'arn:aws:iam::123456789012:user/MyUser',
				'user_id'    => 'AIDAEXAMPLEUSERID',
			),
			$array
		);
	}
}
