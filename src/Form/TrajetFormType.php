<?php

namespace App\Form;

use App\Entity\Trajet;
use App\Entity\Vehicule;
use App\Entity\Preference;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;


class TrajetFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('villeDepart', TextType::class, [
                'label' => 'Ville de départ',
                'attr' => ['class' => 'form-control border']
            ])

            ->add('villeArrivee', TextType::class, [
                'label' => "Ville d'arrivée",
                'attr' => ['class' => 'form-control border']
            ])

            

            ->add('dateDepart', DateTimeType::class, [
                'widget' => 'single_text',
                'html5' => false,
                'attr' => [
                    'class' => 'form-control js-datetime border'
                ]
            ])

            ->add('dateArrivee', DateTimeType::class, [
                'widget' => 'single_text',
                'html5' => false,
                'attr' => [
                    'class' => 'form-control js-datetime border'
                ]
            ])

            ->add('prix', NumberType::class, [
                'label' => 'Prix (€)',
                'attr' => ['class' => 'form-control border', 'min' => 0]
            ])

            ->add('placesDispo', IntegerType::class, [
                'label' => 'Places disponibles',
                'attr' => ['class' => 'form-control', 'min' => 1]
            ])

            ->add('vehicule', EntityType::class, [
                'class' => Vehicule::class,
                'choice_label' => function (Vehicule $vehicule) {
                    return $vehicule->getMarque().' '.$vehicule->getModele();
                },
                'placeholder' => 'Choisir un véhicule',
                'attr' => ['class' => 'form-select']
            ])

            ->add('preferences', EntityType::class, [
                'class' => Preference::class,
                'choice_label' => 'nom',
                'multiple' => true,
                'expanded' => true // checkboxes
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Trajet::class,
        ]);
    }
}