<?php

namespace App\Entity;

use OpenApi\Annotations as OA;
use OpenApi\Annotations\Schema;

/**
 * @Schema(
 *  schema="Teachers",
 * 	title="Teachers",
 * 	description="Teacher Model"
 * )
 */
class Teacher
{
	/**
	 * @OA\Property(type="integer")
	 */
	private $id ;

	/**
	 * @OA\Property(type="string")
	 */
	private $lastName ;

	/**
	 * @OA\Property(type="string")
	 */
	private $firstName ;

	/**
	 * @OA\Property(type="string", property="phoneNumber")
	 */
	private $phoneNumber ;

	/**
	 * @OA\Property(type="number", property="cndPaid")
	 */
	private $cndPaid ;

	/**
	 * @OA\Property(type="string")
	 */
	private $email ;

	/**
	 * @OA\Property(type="integer", property="school_id")
	 */
	private $school_id ; // new 2023

	/**
	 * @OA\Property(type="integer", property="cndMember")
	 */
	private $cndMember;		// adhesion oui/non, nouveauté 2017

	/**
	 * @OA\Property(type="string")
	 */
	private $digitalPrint;	// nouveauté 2017-2018 : empreinte formée par nom+prenom+date_naissance

	/**
	 * @OA\Property(type="integer")
	 */	
	private $nbFiles;

	/**
	 * @OA\Property(type="string")
	 */
	private $schoolName;	
	/**
	 * @OA\Property(type="string")
	 */
	private $editDate;

	/**
	 * @OA\Property(type="integer")
	 */
	private $sentCard;

	/**
	 * @OA\Property(type="integer")
	 */
	private $validatedPhoto;

	/**
	 * @OA\Property(type="integer")
	 */
	private $validatedBirthday;

	/**
	 * @OA\Property(type="string")
	 */
	private $comment;

	/**
	 * @OA\Property(type="integer")
	 */
	private $alreadyPaid;

	/**
	 * @OA\Property(type="string")
	 */
	private $photoName;

	/**
	 * @OA\Property(type="string")
	 */
	private $cniName;

	/**
	 * @OA\Property(type="integer")
	 */
	private $photo_id;
	/**
	 * @OA\Property(type="integer")
	 */
	private $cni_id;
}