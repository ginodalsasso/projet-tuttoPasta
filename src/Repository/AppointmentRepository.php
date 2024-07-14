<?php

namespace App\Repository;

use App\Entity\Appointment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Appointment>
 */
class AppointmentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Appointment::class);
    }

    public function findAllRDV($startDate)
    {
        // Initialisation des temps de début et de fin de la journée de travail
        $startTime = clone $startDate;
        $startTime->setTime(9, 0, 0);

        $endTime = clone $startTime;
        $endTime->setTime(17, 0, 0);

        // Création d'une requête pour trouver tous les rendez-vous existants pour cette journée
        $booking = $this->createQueryBuilder('a')
            ->select('a.startDate')
            ->andWhere('a.startDate >= :start')
            ->andWhere('a.endDate <= :end')
            ->setParameter('start', $startTime)
            ->setParameter('end', $endTime)
            ->getQuery()
            ->getResult();

        // Génération de tous les créneaux horaires possibles pour la journée
        $bookedSlots = [];
        foreach($booking as $booked) {
            $bookedSlots[] = $booked["startDate"]->format('Y-m-d H:i:s');
        }
        // Génération des créneaux disponibles
        $interval = new \DateInterval('PT1H'); // Intervalle d'une heure
        $slots = [];

        // Parcours de la journée par tranches d'une heure
        for ($time = clone $startTime; $time < $endTime; $time->add($interval)) {
            $slot = $time->format('Y-m-d H:i:s');

            // Si le créneau n'est pas déjà réservé, on l'ajoute aux créneaux disponibles
            if (!in_array($slot, $bookedSlots)) {
                $slots[] = $slot;
            }
        }
        // Retourne un tableau contenant les créneaux disponibles et les créneaux réservés
        return [$slots, $bookedSlots];
    }

    //    /**
    //     * @return Appointment[] Returns an array of Appointment objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('a')
    //            ->andWhere('a.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('a.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Appointment
    //    {
    //        return $this->createQueryBuilder('a')
    //            ->andWhere('a.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
