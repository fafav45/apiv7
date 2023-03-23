<?php

namespace App\Entity;

use OpenApi\Annotations as OA;
use OpenApi\Annotations\Schema;

/**
 * @Schema(
 *  schema="Dashboard",
 * 	title="Dashboard",
 * 	description="Dashboard Model"
 * )
 */
class Dashboard
{
	/**
	 * @OA\Property(type="integer")	
	 */
	private $id;

	/**
	* @OA\Property(type="integer")
	*/
	protected $nb_candidates;

	/**
	* @OA\Property(type="integer", description="number of teachers")
	*/
	protected $nb_teachers;

	/**
	* @OA\Property(type="integer", description="number of individuals")
	*/
	protected $nb_individuals;

	/**
	* @OA\Property(type="integer", description="number of duets")
	*/
	protected $nb_duets;

	/**
	* @OA\Property(type="integer", description="number of groups")
	*/
	protected $nb_groups;
	/**
	* @OA\Property(type="integer", description="total number of candidates in groups")
	*/
	protected $nb_total_groups;
	/**
	* @OA\Property(type="integer", description="number of used schools without free candidates")
	*/
	protected $nb_schools;

	/**
	* @OA\Property(type="integer", description="number of candidates membership")
	*/
	protected $countNbCandidateMembership;

	/**
	* @OA\Property(type="integer", description="number of teachers membership")
	*/
	protected $countNbTeacherMembership;

}