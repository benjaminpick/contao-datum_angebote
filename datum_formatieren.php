<?php if (!defined('TL_ROOT')) die('You cannot access this file directly!');
    /**
     * Contao Open Source CMS
     * Copyright (C) 2005-2010 Leo Feyer
     *
     * Formerly known as TYPOlight Open Source CMS.
     *
     * This program is free software: you can redistribute it and/or
     * modify it under the terms of the GNU General Public
     * License as published by the Free Software Foundation, either
     * version 2 of the License, or (at your option) any later version.
     *
     * This program is distributed in the hope that it will be useful,
     * but WITHOUT ANY WARRANTY; without even the implied warranty of
     * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
     * Lesser General Public License for more details.
     *
     * You should have received a copy of the GNU General Public
     * License along with this program. If not, please visit the Free
     * Software Foundation website at <http://www.gnu.org/licenses/>.
     *
     * PHP version 5
     * @copyright  Benjamin Pick
     * @author     Benjamin Pick 
     * @package    datum_angebote
     * @license    GPLv2 or later
     * @filesource
     */


class DatumFormatieren
{


    /**
     * Convert input data
     *
     * @param array     $entry          SCatalog entry array
     * @param string    $field_date     Name of field where the date is in.
     * @param string    $field_time     Name of field where the hours are in (optional)
     * @return array    getdate array
     */
    function get_date_from_entry($entry, $field_date, $field_time = '')
    {
        if (headers_sent())
        {
            echo '<!--';
            var_dump($entry);
            echo '-->';
        }

        if (is_null($entry['data'][$field_date]['raw']))
            return NULL;

        $date = getdate((int)$entry['data'][$field_date]['raw']);

        if ($field_time && isset($entry['data'][$field_time])) {
            if (is_null($entry['data'][$field_time]['raw']))
                $date['hours'] = 0;
            else
			{
				/* Use one of these chars as seperator between hours and minutes. */
				$splitchars = ' :.h';


				$hours = strtok($entry['data'][$field_time]['raw'], $splitchars);
				$minutes = strtok($splitchars);

                $date['hours'] = (int) $hours;
				$date['minutes'] = (int) $minutes;
			}
        }

        return $date;
    }

    function get_month_text($mon)
    {
        return $GLOBALS['TL_LANG']['MONTHS'][$mon - 1];
    }

    function get_uhrzeit($get_date)
    {
        $uhrzeit = $get_date['hours'];
        if ($get_date['minutes'])
            $uhrzeit .= ':' . $get_date['minutes'];
        $uhrzeit .= $GLOBALS['TL_LANG']['datum_angebote']['uhrzeit'];

        return $uhrzeit;
    }

    function get_start_unix($entry)
    {
    	$start = $this->get_date_from_entry($entry, 'cal_start', 'cal_zeit_start');
    	return mktime($start['hours'], $start['minutes'], 0, $start['mon'], $start['mday'], $start['year']);	
    }

    /* Desired output:

    3. Oktober 2012 (Beginn: 10 Uhr, Ende: 17 Uhr)

    bzw.

    3. - 5. Oktober 2012 (Beginn: 10 Uhr, Ende: 17 Uhr)
    */
    function datum_formatieren($entry)
    {
        $start = $this->get_date_from_entry($entry, 'cal_start', 'cal_zeit_start');
        $ende =  $this->get_date_from_entry($entry, 'cal_ende', 'cal_zeit_ende');

        if (is_null($ende)) {
            $formatted_date = $start['mday'] . '. ' . $this->get_month_text($start['mon']) . ' ' . $start['year'];
        } elseif ($start['year'] != $ende['year']) {
            $formatted_date = $start['mday'] . '. ' . $this->get_month_text($start['mon']) . ' ' . $start['year'] . ' - ';
            $formatted_date .= $ende['mday'] . '. ' . $this->get_month_text($ende['mon'])  . ' ' . $ende['year'];
        } elseif ($start['mon'] != $ende['mon']) {
            $formatted_date = $start['mday'] . '. ' . $this->get_month_text($start['mon']) . ' - ';
            $formatted_date .= $ende['mday'] . '. ' . $this->get_month_text($ende['mon'])  . ' ' . $ende['year'];
        } elseif ($start['mday'] != $ende['mday']) {
            $formatted_date = $start['mday'] . '. - ';
            $formatted_date .= $ende['mday'] . '. ' . $this->get_month_text($ende['mon']) . ' ' . $ende['year'];
        } else // single day
        {
            $formatted_date = $ende['mday'] . '. ' . $this->get_month_text($ende['mon']) . ' ' . $ende['year'];
        }

        return $formatted_date;
    }

    function uhrzeit_formatieren($entry)
    {
		if (is_null($entry['data']['cal_ende']['raw']))
			$entry['data']['cal_ende']['raw'] = $entry['data']['cal_start']['raw'];
        $start = $this->get_date_from_entry($entry, 'cal_start', 'cal_zeit_start');
        $ende = $this->get_date_from_entry($entry, 'cal_ende', 'cal_zeit_ende');

        $formatted_date = '';
        if ($start['hours'] && $ende['hours']) {
            $formatted_date .= ' (' . $GLOBALS['TL_LANG']['datum_angebote']['begin'] . ': ' . $this->get_uhrzeit($start) . ', ' . $GLOBALS['TL_LANG']['datum_angebote']['end'] . ': ' . $this->get_uhrzeit($ende) . ')';
        } elseif ($start['hours']) {
            $formatted_date .= ' (' . $GLOBALS['TL_LANG']['datum_angebote']['begin'] . ': ' . $this->get_uhrzeit($start) . ')';
        }

        return $formatted_date;
    }

    function replaceInsertTags($strTag)
    {
        $elements = explode('::', $strTag);

        global $objPage;
        $entry_id = $objPage->id; // Wie komme ich zum entry?

        $ret = false;
        switch (strtolower($elements[0]))
        {
            case 'datum_formatieren':
                $ret = $this->datum_formatieren($entry);
                break;
            case 'uhrzeit_formatieren':
                $ret = $this->uhrzeit_formatieren($entry);

                break;
        }

        return $ret;
    }

}


$GLOBALS['TL_HOOKS']['replaceInsertTags'][] = array('DatumFormatieren', 'replaceInsertTags');
