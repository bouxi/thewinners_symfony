<?php

declare(strict_types=1);

namespace App\Form\EventSubscriber;

use App\Entity\Personnage;
use App\Service\WowData;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;

final class PersonnageSpecSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            FormEvents::PRE_SET_DATA => 'onPreSetData',
            FormEvents::PRE_SUBMIT   => 'onPreSubmit',
        ];
    }

    public function onPreSetData(FormEvent $event): void
    {
        $data = $event->getData();
        $form = $event->getForm();

        if (!$data instanceof Personnage) {
            return;
        }

        $this->addSpecField($form, $data->getClass());
    }

    public function onPreSubmit(FormEvent $event): void
    {
        $submittedData = $event->getData();
        $form = $event->getForm();

        $class = null;

        if (is_array($submittedData)) {
            $class = $submittedData['class'] ?? null;
        }

        $this->addSpecField($form, $class);
    }

    private function addSpecField(FormInterface $form, ?string $class): void
    {
        $choices = [];

        if ($class && isset(WowData::CLASSES[$class])) {
            foreach (WowData::CLASSES[$class] as $spec) {
                $choices[$spec] = $spec;
            }
        }

        $form->add('spec', ChoiceType::class, [
            'label' => 'Spécialisation',
            'choices' => $choices,
            'placeholder' => $class ? '— Choisir —' : '— Choisir d’abord une classe —',
            'required' => true,
            'disabled' => !$class,
        ]);
    }
}