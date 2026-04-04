<?php

namespace App\Repository;

use App\Entity\AppSetting;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<AppSetting>
 */
class AppSettingRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AppSetting::class);
    }

    public function getValue(string $key, string $default = ''): string
    {
        $setting = $this->findOneBy(['key' => $key]);
        return $setting ? $setting->getValue() : $default;
    }

    public function setValue(string $key, string $value): void
    {
        $setting = $this->findOneBy(['key' => $key]);

        if (!$setting) {
            $setting = new AppSetting();
            $setting->setKey($key);
            $this->getEntityManager()->persist($setting);
        }

        $setting->setValue($value);
        $this->getEntityManager()->flush();
    }
}
