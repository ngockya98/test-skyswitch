<?php

declare(strict_types=1);

namespace Amasty\CompanyAccount\Model;

class CountryDataProvider
{
    /**
     * @var \Magento\Directory\Api\CountryInformationAcquirerInterface
     */
    private $countryInformationAcquirer;

    /**
     * @var array
     */
    private $countriesList;

    /**
     * @var \Magento\Framework\Locale\ResolverInterface
     */
    private $resolver;

    /**
     * @var \Magento\Framework\Stdlib\ArrayUtils
     */
    private $arrayUtils;

    public function __construct(
        \Magento\Directory\Api\CountryInformationAcquirerInterface $countryInformationAcquirer,
        \Magento\Framework\Stdlib\ArrayUtils $arrayUtils,
        \Magento\Framework\Locale\ResolverInterface $resolver
    ) {
        $this->countryInformationAcquirer = $countryInformationAcquirer;
        $this->arrayUtils = $arrayUtils;
        $this->resolver = $resolver;
    }

    /**
     * @return array
     */
    public function getCountriesList()
    {
        if (!$this->countriesList) {
            $this->countriesList = [];
            $countries = $this->countryInformationAcquirer->getCountriesInfo();
            if ($countries) {
                foreach ($countries as $country) {
                    $this->countriesList[$country->getFullNameLocale()] = $country->getId();
                }
            }

            $this->arrayUtils->ksortMultibyte($this->countriesList, $this->resolver->getLocale());
            $this->countriesList = array_flip($this->countriesList);
        }

        return $this->countriesList;
    }

    /**
     * @param string $code
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getCountryNameByCode($code)
    {
        $countryName = '';
        if ($code) {
            $country = $this->countryInformationAcquirer->getCountryInfo($code);
            if ($country && $country->getId()) {
                $countryName = $country->getFullNameLocale();
            }
        }

        return $countryName;
    }

    /**
     * @param int $countryId
     * @param int $regionId
     * @param string $regionName
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getRegionName($countryId, $regionId, $regionName)
    {
        $country = $this->countryInformationAcquirer->getCountryInfo($countryId);
        $regions = $country->getAvailableRegions();
        if ($regions && count($regions)) {
            foreach ($regions as $region) {
                if ($regionId == $region->getId()) {
                    $regionName = $region->getName();
                    break;
                }
            }
        }

        return $regionName;
    }
}
