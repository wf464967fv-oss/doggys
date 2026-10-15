<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

/**
 * The 19 professional categories of the specification. Idempotent (upsert on slug).
 */
class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $sort = 0;

        foreach ($this->categories() as $slug => [$group, $icon, $isRegulated, $name, $description]) {
            Category::updateOrCreate(['slug' => $slug], [
                'group' => $group,
                'icon' => $icon,
                'is_regulated' => $isRegulated,
                'name' => $name,
                'description' => $description,
                'sort' => $sort += 10,
            ]);
        }
    }

    /**
     * @return array<string, array{0: string, 1: string, 2: bool, 3: array<string, string>, 4: array<string, string>}>
     */
    private function categories(): array
    {
        return [
            'toiletteur-salon' => ['grooming', 'scissors', false,
                ['fr' => 'Toiletteur en salon', 'nl' => 'Hondentrimsalon', 'en' => 'Grooming salon', 'de' => 'Hundesalon'],
                ['fr' => 'Bain, tonte, coupe et soins du pelage dans un salon équipé.', 'nl' => 'Wassen, scheren, knippen en vachtverzorging in een ingericht salon.', 'en' => 'Bathing, clipping, trimming and coat care in a fully equipped salon.', 'de' => 'Baden, Scheren, Trimmen und Fellpflege in einem ausgestatteten Salon.']],
            'toiletteur-domicile' => ['grooming', 'van', false,
                ['fr' => 'Toiletteur à domicile', 'nl' => 'Trimmer aan huis', 'en' => 'Mobile groomer', 'de' => 'Mobiler Hundefriseur'],
                ['fr' => 'Le toilettage vient à vous, idéal pour les chiens stressés ou âgés.', 'nl' => 'De trimmer komt naar u toe, ideaal voor gestreste of oudere honden.', 'en' => 'Grooming comes to your door, ideal for anxious or older dogs.', 'de' => 'Die Fellpflege kommt zu Ihnen – ideal für ängstliche oder ältere Hunde.']],
            'veterinaire' => ['health', 'stethoscope', true,
                ['fr' => 'Vétérinaire', 'nl' => 'Dierenarts', 'en' => 'Vet', 'de' => 'Tierarzt'],
                ['fr' => 'Consultations, vaccins, suivi de santé et chirurgie.', 'nl' => 'Consultaties, vaccinaties, gezondheidsopvolging en chirurgie.', 'en' => 'Check-ups, vaccinations, health monitoring and surgery.', 'de' => 'Sprechstunden, Impfungen, Gesundheitsvorsorge und Chirurgie.']],
            'urgences-24h' => ['health', 'siren', true,
                ['fr' => 'Urgences vétérinaires 24h/24', 'nl' => 'Dierenartsspoed 24/7', 'en' => '24/7 emergency vet', 'de' => 'Tierärztlicher Notdienst 24/7'],
                ['fr' => 'Cliniques ouvertes jour et nuit, week-ends et jours fériés.', 'nl' => 'Klinieken die dag en nacht open zijn, ook in het weekend en op feestdagen.', 'en' => 'Clinics open day and night, including weekends and bank holidays.', 'de' => 'Kliniken, die Tag und Nacht geöffnet sind – auch am Wochenende und an Feiertagen.']],
            'osteopathe' => ['health', 'bone', false,
                ['fr' => 'Ostéopathe animalier', 'nl' => 'Dierenosteopaat', 'en' => 'Animal osteopath', 'de' => 'Tierosteopath'],
                ['fr' => 'Approche manuelle pour soulager raideurs et douleurs.', 'nl' => 'Manuele aanpak om stijfheid en pijn te verlichten.', 'en' => 'A hands-on approach to ease stiffness and discomfort.', 'de' => 'Manuelle Behandlung zur Linderung von Steifheit und Schmerzen.']],
            'kine-physio' => ['health', 'activity', false,
                ['fr' => 'Kiné & physiothérapie', 'nl' => 'Kinesitherapie & fysiotherapie', 'en' => 'Physiotherapy', 'de' => 'Physiotherapie'],
                ['fr' => 'Rééducation après une opération ou une blessure.', 'nl' => 'Revalidatie na een operatie of blessure.', 'en' => 'Rehabilitation after surgery or injury.', 'de' => 'Rehabilitation nach einer Operation oder Verletzung.']],
            'hydrotherapie' => ['health', 'waves', false,
                ['fr' => 'Hydrothérapie', 'nl' => 'Hydrotherapie', 'en' => 'Hydrotherapy', 'de' => 'Hydrotherapie'],
                ['fr' => 'Exercices dans l’eau, doux pour les articulations.', 'nl' => 'Oefeningen in het water, zacht voor de gewrichten.', 'en' => 'Water-based exercise that is gentle on the joints.', 'de' => 'Bewegung im Wasser, schonend für die Gelenke.']],
            'dentisterie' => ['health', 'tooth', true,
                ['fr' => 'Dentisterie vétérinaire', 'nl' => 'Dierentandheelkunde', 'en' => 'Veterinary dentistry', 'de' => 'Tierzahnheilkunde'],
                ['fr' => 'Détartrage, soins et chirurgie dentaire.', 'nl' => 'Tandsteen verwijderen, tandverzorging en tandchirurgie.', 'en' => 'Scaling, dental care and oral surgery.', 'de' => 'Zahnsteinentfernung, Zahnpflege und Zahnchirurgie.']],
            'educateur' => ['education', 'graduation-cap', false,
                ['fr' => 'Éducateur canin', 'nl' => 'Hondentrainer', 'en' => 'Dog trainer', 'de' => 'Hundetrainer'],
                ['fr' => 'Cours individuels ou collectifs, du chiot à l’adulte.', 'nl' => 'Individuele of groepslessen, van pup tot volwassen hond.', 'en' => 'One-to-one or group classes, from puppy to adult.', 'de' => 'Einzel- oder Gruppenstunden, vom Welpen bis zum erwachsenen Hund.']],
            'comportementaliste' => ['education', 'message-circle', false,
                ['fr' => 'Comportementaliste', 'nl' => 'Gedragstherapeut', 'en' => 'Behaviourist', 'de' => 'Verhaltenstherapeut'],
                ['fr' => 'Aide face à l’anxiété, la réactivité ou l’agressivité.', 'nl' => 'Hulp bij angst, reactiviteit of agressie.', 'en' => 'Support with anxiety, reactivity or aggression.', 'de' => 'Hilfe bei Angst, Reaktivität oder Aggression.']],
            'pet-sitter' => ['care', 'key', false,
                ['fr' => 'Pet-sitter', 'nl' => 'Petsitter', 'en' => 'Pet sitter', 'de' => 'Tiersitter'],
                ['fr' => 'Garde et visites chez vous pendant votre absence.', 'nl' => 'Oppas en bezoekjes bij u thuis tijdens uw afwezigheid.', 'en' => 'Sitting and home visits while you are away.', 'de' => 'Betreuung und Besuche bei Ihnen zu Hause, während Sie unterwegs sind.']],
            'promeneur' => ['care', 'route', false,
                ['fr' => 'Promeneur de chiens', 'nl' => 'Hondenuitlaatdienst', 'en' => 'Dog walker', 'de' => 'Gassigeher'],
                ['fr' => 'Balades régulières pour un chien heureux et dépensé.', 'nl' => 'Regelmatige wandelingen voor een blije, uitgeleefde hond.', 'en' => 'Regular walks for a happy, well-exercised dog.', 'de' => 'Regelmäßige Spaziergänge für einen glücklichen, ausgelasteten Hund.']],
            'garderie' => ['care', 'sun', false,
                ['fr' => 'Garderie canine', 'nl' => 'Hondendagopvang', 'en' => 'Doggy daycare', 'de' => 'Hundetagesstätte'],
                ['fr' => 'Accueil à la journée, jeux et socialisation.', 'nl' => 'Dagopvang met spel en socialisatie.', 'en' => 'Daytime care with play and socialising.', 'de' => 'Tagesbetreuung mit Spiel und Sozialkontakt.']],
            'pension' => ['care', 'bed', false,
                ['fr' => 'Pension canine', 'nl' => 'Hondenpension', 'en' => 'Boarding kennel', 'de' => 'Hundepension'],
                ['fr' => 'Hébergement de quelques nuits à plusieurs semaines.', 'nl' => 'Verblijf van enkele nachten tot meerdere weken.', 'en' => 'Stays from a few nights to several weeks.', 'de' => 'Unterbringung von wenigen Nächten bis zu mehreren Wochen.']],
            'eleveur-agree' => ['adoption', 'award', true,
                ['fr' => 'Éleveur agréé', 'nl' => 'Erkende fokker', 'en' => 'Licensed breeder', 'de' => 'Anerkannter Züchter'],
                ['fr' => 'Élevages disposant d’un agrément officiel.', 'nl' => 'Kwekers met een officiële erkenning.', 'en' => 'Breeders holding an official licence.', 'de' => 'Zuchtstätten mit offizieller Zulassung.']],
            'refuge' => ['adoption', 'home-heart', true,
                ['fr' => 'Refuge', 'nl' => 'Dierenasiel', 'en' => 'Animal shelter', 'de' => 'Tierheim'],
                ['fr' => 'Des chiens qui attendent une famille pour la vie.', 'nl' => 'Honden die wachten op een gezin voor het leven.', 'en' => 'Dogs waiting for a family for life.', 'de' => 'Hunde, die auf eine Familie fürs Leben warten.']],
            'animalerie' => ['shop', 'shopping-bag', false,
                ['fr' => 'Animalerie', 'nl' => 'Dierenwinkel', 'en' => 'Pet shop', 'de' => 'Tierhandlung'],
                ['fr' => 'Alimentation, accessoires et conseils près de chez vous.', 'nl' => 'Voeding, accessoires en advies bij u in de buurt.', 'en' => 'Food, accessories and advice close to home.', 'de' => 'Futter, Zubehör und Beratung in Ihrer Nähe.']],
            'lieu-dog-friendly' => ['places', 'coffee', false,
                ['fr' => 'Lieu dog-friendly', 'nl' => 'Hondvriendelijke plek', 'en' => 'Dog-friendly place', 'de' => 'Hundefreundlicher Ort'],
                ['fr' => 'Cafés, restaurants et hébergements où votre chien est le bienvenu.', 'nl' => 'Cafés, restaurants en logies waar uw hond welkom is.', 'en' => 'Cafés, restaurants and stays where your dog is welcome.', 'de' => 'Cafés, Restaurants und Unterkünfte, in denen Ihr Hund willkommen ist.']],
            'parc-a-chiens' => ['places', 'trees', false,
                ['fr' => 'Parc à chiens', 'nl' => 'Hondenlosloopweide', 'en' => 'Dog park', 'de' => 'Hundeauslaufgebiet'],
                ['fr' => 'Espaces clôturés pour courir en liberté.', 'nl' => 'Omheinde zones waar uw hond vrij kan rennen.', 'en' => 'Fenced areas where dogs can run off the lead.', 'de' => 'Eingezäunte Flächen zum freien Toben.']],
        ];
    }
}
