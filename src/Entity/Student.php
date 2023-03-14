<?php

namespace App\Entity;

use OpenApi\Annotations as OA;
use Doctrine\ORM\Mapping as ORM;

/**
 * @OA\Schema(
 *  schema="Students",
 * 	title="Students",
 * 	description="Student Model"
 * )
 * @ORM\Entity(repositoryClass=TeacherRepository::class)
 */
class Student
{

	/**
	 * @OA\Property(type="integer")
	 * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
	 */
	private $id;

	/**
	 * @OA\Property(type="string", property="lastName")
	 */
	private $lastName;

	/**
	 * @OA\Property(type="string", property="firstName")
	 */
	private $firstName;

	/**
	 * @OA\Property(type="string", description="ISO format", example="1989-08-11")
	 */
	private $birthday; // format ISO

	/**
	 * @OA\Property(type="integer")
	 */
	private $age;

	/**
	 * @OA\Property(type="string")
	 */
	private $address;

	/**
	 * @OA\Property(type="string", property="additionalAddress")
	 */
	private $additionalAddress;

	/**
	 * @OA\Property(type="string")
	 */
	private $city;

	/**
	 * @OA\Property(type="string")
	 */
	private $postalCode;

	/**
	 * @OA\Property(type="string")
	 */
	private $email;

	/**
	 * @OA\Property(type="string")
	 */
	private $phoneNumber1;

	/**
	 * @OA\Property(type="string")
	 */
	private $phoneNumber2;

	/**
	 * @OA\Property(type="string", description="F:Female, M:Male")
	 */
	private $genre;

	/**
	 * @OA\Property(type="string", description="CNI validated")
	*/	
	private $valid;		// candidat valide (par rapport à la CNI)
	
	/**
	 * @OA\Property(type="number", description="paid memberShip")
	*/	
	private $cndPaid;		// montant paiement adhesion

	/**
	 * @OA\Property(type="number", description="paid individual")
	*/	
	private $indPaid;		// montant paiement inscription individuel

	/**
	 * @OA\Property(type="number", description="paid duet")
	*/
	private $duoPaid;		// montant paiement inscription duo

	/**
	 * @OA\Property(type="number", description="paid group")
	*/
	private $grpPaid;		// montant paiement inscription groupe

	/**
	 * @OA\Property(type="integer", description="memberShip, 1=true, 0=false")
	*/	
	private $cndMember;		// adhesion oui/non, nouveauté 2017

	/**
	 * @OA\Property(type="string")
	*/	
	private $digitalPrint;	// nouveauté 2017-2018 : empreinte formée par nom+prenom+date_naissance

	/**
	 * @OA\Property(type="integer")
	*/	
	private $school_id;

	/**
	 * @OA\Property(type="integer")
	*/	
	private $validatedPhoto;

	/**
	 * @OA\Property(type="string", property="schoolName")
	*/	
	private $schoolName;

	/**
	 * @OA\Property(type="integer", description="boardMemberShip, 1=true, 0=false")
	*/	
	private $cndBoardMember;		// adhesion oui/non, nouveauté 2017-2018

	/**
	 * @OA\Property(type="string", example="2021-11-14 20:05:19")
	*/	
	private $editDate;

	/**
	 * @OA\Property(type="integer", description="validation, 1=true, 0=false")
	*/	
	private $minorCertificate;

	/**
	 * @OA\Property(type="integer", description="validation, 1=true, 0=false")
	*/	
	private $majorCertificate;

	/**
	 * @OA\Property(type="integer", description="1=true, 0=false")
	*/	
	private $sentCard;

	/**
	 * @OA\Property(type="string")
	*/	
	private $comment;

	/**
	 * @OA\Property(type="integer", description="number of attached files")
	*/	
	private $nbFiles;

	/**
	 * @OA\Property(type="integer", description="number of individual entries")
	*/	
	private $nb_individuals;

	/**
	 * @OA\Property(type="integer", description="number of duet entries")
	*/	
	private $nb_duets;

	/**
	 * @OA\Property(type="integer", description="number of group entries")
	*/	
	private $nb_groups;

	/**
	 * @OA\Property(type="integer", description="number of entries")
	*/	
	private $nb_entries;

	/**
	 * @OA\Property(type="string")
	*/	
	private $schoolPostalCode;

	/**
	 * @OA\Property(type="integer", description="found in other school")
	*/	
	private $alreadyPaid;

	/**
	 * @OA\Property(type="string")
	*/
	private $minorCertificateName;

	/**
	 * @OA\Property(type="string")
	*/
	private $majorCertificateName;

	/**
	 * @OA\Property(type="integer")
	*/	
	private $minorCertificate_id;

	/**
	 * @OA\Property(type="integer")
	*/	
	private $majorCertificate_id;

}