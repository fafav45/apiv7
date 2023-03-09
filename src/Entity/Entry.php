<?php

namespace App\Entity;

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *  schema="Entries",
 * 	title="Entries",
 * 	description="Entries Model"
 * )
 */
class Entry
{

	/**
	 * @OA\Property(type="integer")
	 */
	private $id;

	/**
	 * @OA\Property(type="integer")
	 */
	private $class_id;

	/**
	 * @OA\Property(type="integer")
	 */
	private $style_id;

	/**
	 * @OA\Property(type="integer")
	 */
	private $categorie_id;

	/**
	 * @OA\Property(type="integer")
	 */
	private $track;

	/**
	 * @OA\Property(type="string")
	 */
	private $duration;

	/**
	 * @OA\Property(type="string")
	 */
	private $placement;

	/**
	 * @OA\Property(type="string")
	 */
	private $school_id;

	/**
	 * @OA\Property(type="string", nullable=true)
	 */
	private $title; // ballet

	/**
	 * @OA\Property(type="string", nullable=true)
	 */
	private $song;

	/**
	 * @OA\Property(type="string", nullable=true)
	 */
	private $author;

	/**
	 * @OA\Property(type="integer")
	 */
	private $teacher_id;

	/**
	 * @OA\Property(type="integer", description="0:false, 1:true")
	 */
	private $isNational;

	/**
	 * @OA\Property(type="integer", description="0:false, 1:true")
	 */
	private $receivedMP3;

	/**
	 * @OA\Property(type="string", nullable=true)
	 */
	private $styleName;

	/**
	 * @OA\Property(type="string", nullable=true)
	 */
	private $divisionName;

	/**
	 * @OA\Property(type="string", nullable=true)
	 */
	private $categoryName;

	/**
	 * @OA\Property(type="string", nullable=true)
	 */	
	private $teacherLastName;

	/**
	 * @OA\Property(type="string", nullable=true)
	 */
	private $teacherFirstName;

	/**
	 * @OA\Property(type="integer", description="style is active")
	 */
	private $isStyleActive;

	/**
	 * @OA\Property(type="integer", example="2")
	 */
	private $type_id=0;

	/**
	 * @OA\Property(type="String", example="individual")
	 */
	private $entryType;

	/**
	 * @OA\Property(type="String")
	 */
	private $editDate;

	/**
	 * @OA\Property(type="integer", example="0", description="0=false, 1=true")
	 */
	private $validatedMP3;

	/**
	 * @OA\Property(type="String", nullable=true)
	 */
	private $comment;

	/**
	 * @OA\Property(type="integer", description="between 1 and 12")
	 */
	private $nbCandidates;

	/**
	 * @OA\Property(type="String")
	 */
	private $schoolName;

/*  ----- only for mp3 ------ */

	/**
	 * @OA\Property(type="String", example="GRP11.MP3", nullable=true, description="only used for followUp tool")
	 */
	private $mp3Name;

	/**
	 * @OA\Property(type="integer", example="3", nullable=true, description="only used for followUp tool")
	 */
	private $mp3_id;

	/**
	 * @OA\Property(type="String", example="2021-12-02", nullable=true, description="upload date, only used for followUp tool")
	 */
	private $mp3Date;

}