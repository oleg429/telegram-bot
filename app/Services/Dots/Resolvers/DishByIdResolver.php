<?php
/**
 * Description of DishByIdResolver.php
 * @copyright Copyright (c) MISTER.AM, LLC
 * @author    Egor Gerasimchuk <egor@mister.am>
 */

namespace App\Services\Dots\Resolvers;


use App\Services\Dots\Providers\DotsProvider;

class DishByIdResolver
{
    /** @var DotsProvider */
    private $dotsProvider;

    public function __construct(
        DotsProvider $dotsProvider
    )
    {
        $this->dotsProvider = $dotsProvider;
    }

    /**
     * @param string $name
     * @return array|null
     */
    public function resolve(string $id, string $companyId): ?array
    {
        $dishes = $this->dotsProvider->getMenuItems($companyId);

        if(!$dishes){
            return null;
        }

        foreach ($dishes['items'] as $dishCategory) {

            foreach ($dishCategory['items'] as $dish){
                if ($dish['id'] == $id) {
                    return $dish;
                }
            }

        }
        return null;
    }


}
