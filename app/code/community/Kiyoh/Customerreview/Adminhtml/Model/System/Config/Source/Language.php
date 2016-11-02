<?php

/**
* @category   Kiyoh
* @package    Kiyoh_Customerreview
* @author     ModuleCreator
* @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*/
class Kiyoh_Customerreview_Adminhtml_Model_System_Config_Source_Language {

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray() {

        $languges = array(
            '' => '',
            '1' => ('Dutch (BE)'),
            '2' => ('French'),
            '3' => ('German'),
            '4' => ('English'),
            '5' => ('Netherlands'),
            '6' => ('Danish'),
            '7' => ('Hungarian'),
            '8' => ('Bulgarian'),
            '9' => ('Romanian'),
            '10' => ('Croatian'),
            '11' => ('Japanese'),
            '12' => ('Spanish'),
            '13' => ('Italian'),
            '14' => ('Portuguese'),
            '15' => ('Turkish'),
            '16' => ('Norwegian'),
            '17' => ('Swedish'),
            '18' => ('Finnish'),
            '20' => ('Brazilian Portuguese'),
            '21' => ('Polish'),
            '22' => ('Slovenian'),
            '23' => ('Chinese'),
            '24' => ('Russian'),
            '25' => ('Greek'),
            '26' => ('Czech'),
            '29' => ('Estonian'),
            '31' => ('Lithuanian'),
            '33' => ('Latvian'),
            '35' => ('Slovak')
        );
        foreach ($languges as $key => $item) {
            $dataArray[] = array(
                'value'=>$key,
                'label'=>$item
            );
        }
        return $dataArray;
    }

}
