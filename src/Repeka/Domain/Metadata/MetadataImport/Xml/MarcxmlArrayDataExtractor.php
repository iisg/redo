<?php
namespace Repeka\Domain\Metadata\MetadataImport\Xml;

class MarcxmlArrayDataExtractor {

    /** Input:
     * <record
     * xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
     * xsi:schemaLocation="http://www.loc.gov/MARC21/slim http://www.loc.gov/standards/marcxml/schema/MARC21slim.xsd"
     * xmlns="http://www.loc.gov/MARC21/slim">
     * <leader>02573cam a2200457 i 4500</leader>
     * <controlfield tag="005">a</controlfield>
     * <controlfield tag="008">b</controlfield>
     * <datafield tag="024" ind1="8" ind2="0">
     * <subfield code="a">c</subfield>
     * </datafield>
     * <datafield tag="024" ind1="88" ind2="00">
     * <subfield code="a">cc</subfield>
     * </datafield>
     * <datafield tag="035" ind1=" " ind2=" ">
     * <subfield code="a">d</subfield>
     * </datafield>
     * <datafield tag="040" ind1=" " ind2=" ">
     * <subfield code="a">f</subfield>
     * <subfield code="d">g</subfield>
     * <subfield code="d">gg</subfield>
     * </datafield>
     * </record>
     *
     * Output:
     *
     * [
     * '005' => [0 => '20180803001600.0'],
     * '008' => [0 => '040430s1877    pl a   g     |000 0 pol c']
     * '024' => [['ind1'=> '8', 'ind2'=>'0', 'a' => ['c'], 'order'=>['a']],['ind1'=> '88', 'ind2'=>'00', 'a' => ['cc'], 'order'=>['a']]],
     * '035' => [['ind1'=> ' ', 'ind2'=>' ', 'a' => ['d'],  'order'=>['a']]],
     * '040' => [['ind1'=> ' ', 'ind2'=>' ', 'a' => ['f'], 'd'=>['g', 'gg'], 'order'=>['a', 'd', 'd']]]
     * ],
     *
     * ]
     */
    public function import(string $xmlString): array {
        $xmlParser = xml_parser_create();
        xml_parse_into_struct($xmlParser, $xmlString, $object);
        $marcxmlResource = [];
        $tag = null;
        $codes = [];
        foreach ($object as $element) {
            switch ($element['tag']) {
                case 'CONTROLFIELD':
                    $tag = $element['attributes']['TAG'];
                    $marcxmlResource[$tag][] = $element['value'];
                    break;
                case 'DATAFIELD':
                    if ($element['type'] == 'open') {
                        $tag = $element['attributes']['TAG'];
                        $codes = ['order' => []];
                        if (isset($element['attributes']['IND1'])) {
                            $codes['ind1'] = $element['attributes']['IND1'];
                        }
                        if (isset($element['attributes']['IND2'])) {
                            $codes['ind2'] = $element['attributes']['IND2'];
                        }
                    }
                    if ($element['type'] == 'close') {
                        $marcxmlResource[$tag][] = $codes;
                        $codes = [];
                    }
                    break;
                case 'SUBFIELD':
                    $code = $element['attributes']['CODE'];
                    $codes['order'][] = $code;
                    $codes[$code][] = $element['value'];
                    break;
                default:
                    break;
            }
        }
        return $marcxmlResource;
    }
}
