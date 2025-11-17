<?php
namespace App\Helpers;

class EditeurHelper
{
    /**
     * Supprime les balises <div class="ac-img">...</div> et garde leur contenu interne.
     */
    public static function stripAcImgDiv(string $html): string
    {
        return preg_replace('#<div class="ac-img">\s*(.*?)\s*</div>#is', '$1', $html);
    }
}
