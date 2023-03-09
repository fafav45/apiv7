<?php

namespace App\Entity;

use OpenApi\Annotations as OA;
use OpenApi\Annotations\Schema;

/**
 * @Schema(
 *  schema="Musics",
 * 	title="Musics",
 * 	description="Musics Model"
 * )
 */
class Music
{
	/**
	 * @OA\Property(type="integer")
	 */
	private $id;

	/**
	 * @OA\Property(type="string", nullable=true)
	 */
	private $title; // ballet

	/**
	 * @OA\Property(type="string", nullable=true)
	 */
	private $song;

	/**
	 * @OA\Property(type="string")
	 */
	private $duration;

	/**
	 * @OA\Property(type="String")
	 */
	private $schoolName;

	/**
	 * @OA\Property(type="string", nullable=true)
	 */	
	private $teacherLastName;

	/**
	 * @OA\Property(type="integer", example="0", description="0=false, 1=true")
	 */
	private $validatedMP3;

	/**
	 * @OA\Property(type="string", nullable=true)
	 */
	private $styleName;

	/**
	 * @OA\Property(type="string", example="Balet", nullable=true)
	 */
	private $categoryName;

	/**
	 * @OA\Property(type="String", example="Preparatory", nullable=true)
	 */
	private $comment;

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