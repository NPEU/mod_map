<?php

namespace NPEU\Module\Map\Site\Helper;

require_once dirname(dirname(__DIR__)) . '/vendor/autoload.php';


use Joomla\CMS\Application\SiteApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\Database\DatabaseAwareInterface;
use Joomla\Database\DatabaseAwareTrait;
use Joomla\Registry\Registry;

defined('_JEXEC') or die;

/**
 * Helper for mod_map
 *
 * @since  1.5
 */
class MapHelper implements DatabaseAwareInterface
{
    use DatabaseAwareTrait;

    /**
     * Loads JS/CSS
     *
     * @param array $params
     * @return void
     * @access public
     */
    /*public function loadAssets($params): void
    {
        $doc = Factory::getDocument();
        //$doc->addStyleSheet();
        //$doc->addScript();
        $template_path = Uri::getInstance()->root() . '/templates/npeu6';

        $module_path = str_replace($_SERVER['DOCUMENT_ROOT'], '', dirname(dirname(__DIR__)));

        $doc->addStyleSheet($module_path . '/assets/leaflet/leaflet.css');
        $doc->addScript($module_path . '/assets/leaflet/leaflet.js');

        $doc->addStyleSheet($module_path . '/assets/leaflet-fullscreen/Control.FullScreen.css');
        $doc->addScript($module_path . '/assets/leaflet-fullscreen/Control.FullScreen.js');

        $doc->addScript($module_path . '/assets/leaflet-svgicon/leaflet-svg-icon.js');

        $doc->addScript($module_path . '/assets/leaflet-map.js');


        if ($params->get('filterability', false)) {
            // Note I should probably use a CDN for this so it's not template-specific:
            $doc->addScript($template_path . '/js/filter.min.js');
        }

        if ($params->get('sortability', false)) {
            // Note I should probably use a CDN for this so it's not template-specific:
            $doc->addScript($template_path . '/js/sort.min.js');
        }
    }*/

    /**
     * Converts CSV to an array
     *
     * @param  string             $csv          CSV to convert
     * @param  bool               $header_keys  Does the CSV contain a header row?
     * @param  mixed (int|false)  $cols         The number of columns each row is expected to have
     *
     * @return  mixed
     */
    public function csvArray($csv, $header_keys = false, $cols = false)
    {
        if (!is_string($csv)) {
            trigger_error('Function \'csvarray\' expects argument 1 to be an string', E_USER_ERROR);
            return false;
        }

        // Normalise new lines:
        trim(preg_replace('~\r\n?~', "\n", $csv));

        // Remove breaks from within quotes:
        if (preg_match_all('/"[^"]+"/', $csv, $matches)) {
            foreach($matches[0] as $match) {
                $new = preg_replace('/(\n|\r)/', '{_NEWLINE_}', $match);
                $csv = preg_replace('/' . preg_quote($match, '/') . '/', $new, $csv);
            }
        }
        $csv = mb_convert_encoding($csv, 'UTF-8');
        $csv_array = explode(PHP_EOL, $csv);
        $data = [];
        $cell_total = 0;
        $row_count = 0;
        $headers = [];
        // Process each line:
        foreach($csv_array as $line) {
            $cell_count = 0;
            $row_count++;
            if (preg_match_all('/"[^"]+"/', $line, $matches)) {
                foreach($matches[0] as $match) {
                    $new = preg_replace('/,/', '{_COMMA_}', $match);
                    $line = preg_replace('/' . preg_quote($match, '/') . '/', $new, $line);
                }
            }
            $line = preg_replace('/""/', '{_QUOTE_}', $line);
            $line = preg_replace('/"/', '', $line);
            $line = preg_replace('/{_QUOTE_}/', '"', $line);
            $line = preg_replace('/,/', '\n', $line);
            $line = preg_replace('/{_COMMA_}/', ',', $line);
            $line = preg_replace('/\s{2,}/', ' ', $line);
            $line = preg_replace('/{_NEWLINE_}/', "\n", $line);
            $cells = explode('\n', $line);
            if (is_int($cols) && $cols > 1) {
                $cells = array_pad($cells, $cols, '');
            }
            $row = [];

            $i = 0;
            foreach($cells as $cell) {
                $cell_count++;
                $cell = trim(htmlentities($cell, ENT_QUOTES));
                if ($row_count == 1) {
                    if (mb_strlen($cell) > 0) {
                        $cell_total = $cell_count;
                    }
                }
                if ($cell_count <= $cell_total) {
                    if ($header_keys && $row_count == 1) {
                        $headers[] = $cell;
                    }
                    if (!$header_keys || $row_count == 1) {
                        $row[] = $cell;
                    } else {
                        $row[$headers[$i]] = $cell;
                    }
                }
                $i++;
            }
            if (!$header_keys || $row_count == 1) {
                $first = 0;
            } else {
                $first = $headers[0];
            }
            if (mb_strlen($row[$first]) > 0) {
                $data[] = $row;
            }
        }
        return $data;
    }

    /**
     * Strips punctuation from a string
     *
     * @param string $text
     * @return string
     * @access public
     */
    public static function stripPunctuation($text)
    {
        if (!is_string($text)) {
            trigger_error('Function \'strip_punctuation\' expects argument 1 to be an string', E_USER_ERROR);
            return false;
        }
        $text = html_entity_decode($text, ENT_QUOTES);

        $urlbrackets = '\[\]\(\)';
        $urlspacebefore = ':;\'_\*%@&?!' . $urlbrackets;
        $urlspaceafter = '\.,:;\'\-_\*@&\/\\\\\?!#' . $urlbrackets;
        $urlall = '\.,:;\'\-_\*%@&\/\\\\\?!#' . $urlbrackets;

        $specialquotes = '\'"\*<>';

        $fullstop = '\x{002E}\x{FE52}\x{FF0E}';
        $comma = '\x{002C}\x{FE50}\x{FF0C}';
        $arabsep = '\x{066B}\x{066C}';
        $numseparators = $fullstop . $comma . $arabsep;

        $numbersign = '\x{0023}\x{FE5F}\x{FF03}';
        $percent = '\x{066A}\x{0025}\x{066A}\x{FE6A}\x{FF05}\x{2030}\x{2031}';
        $prime = '\x{2032}\x{2033}\x{2034}\x{2057}';
        $nummodifiers = $numbersign . $percent . $prime;
        $return = preg_replace(
            [
                // Remove separator, control, formatting, surrogate,
                // open/close quotes.
                '/[\p{Z}\p{Cc}\p{Cf}\p{Cs}\p{Pi}\p{Pf}]/u',
                // Remove other punctuation except special cases
                '/\p{Po}(?<![' . $specialquotes .
                $numseparators . $urlall . $nummodifiers . '])/u',
                // Remove non-URL open/close brackets, except URL brackets.
                '/[\p{Ps}\p{Pe}](?<![' . $urlbrackets . '])/u',
                // Remove special quotes, dashes, connectors, number
                // separators, and URL characters followed by a space
                '/[' . $specialquotes . $numseparators . $urlspaceafter .
                '\p{Pd}\p{Pc}]+((?= )|$)/u',
                // Remove special quotes, connectors, and URL characters
                // preceded by a space
                '/((?<= )|^)[' . $specialquotes . $urlspacebefore . '\p{Pc}]+/u',
                // Remove dashes preceded by a space, but not followed by a number
                '/((?<= )|^)\p{Pd}+(?![\p{N}\p{Sc}])/u',
                // Remove consecutive spaces
                '/ +/',
            ],
            ' ',
            $text
        );
        $return = str_replace('/', '_', $return);
        return str_replace("'", '', $return);
    }

    /**
     * Creates an HTML-friendly string for use in id's
     *
     * @param string $text
     * @return string
     * @access public
     */
    public function htmlID($text)
    {
        if (!is_string($text)) {
            trigger_error('Function \'html_id\' expects argument 1 to be an string', E_USER_ERROR);
            return false;
        }
        $return = strtolower(trim(preg_replace('/\s+/', '-', $this->stripPunctuation($text))));
        return $return;
    }

    /**
     * Gets a twig instance - useful as we don't have to re-declare customisations each time.
     *
     * @param  array    $tpls   Array of strings bound to template names
     * @return object
     */
    /*public function getTwig(Registry $config, SiteApplication $app): object
    {
        $tpl = $config->get('data_tpl');
        $loader = new \Twig\Loader\ArrayLoader(['tpl' => $tpl]);
        $twig   = new \Twig\Environment($loader);
        //$twig = new Twig_Environment($loader, ['debug' => true]);

        // Add markdown filter:
        $md_filter = new \Twig\TwigFilter('md', function ($string) {
            $new_string = '';
            // Parse md here
            $new_string = Markdown::defaultTransform($string);
            return $new_string;
        });

        $twig->addFilter($md_filter);
        // Use like {{ var|md|raw }}

        // Add pad filter:
        $pad_filter = new \Twig\TwigFilter('pad', function ($string, $length, $pad = ' ', $type = 'right') {
            $new_string = '';
            switch ($type) {
                case 'right':
                    $type = STR_PAD_RIGHT;
                    break;
                case 'left':
                    $type = STR_PAD_LEFT;
                    break;
                case 'both':
                    break;
                    $type = STR_PAD_BOTH;
            }
            $length = (int) $length;
            $pad    = (string) $pad;
            $new_string = str_pad($string, $length, $pad, $type);

            return $new_string;
        });
        $twig->addFilter($pad_filter);

        // Add regex_replace filter:
        $regex_replace_filter = new \Twig\TwigFilter('regex_replace', function ($string, $search = '', $replace = '') {
            $new_string = '';

            $new_string = preg_replace($search, $replace, $string);

            return $new_string;
        });
        $twig->addFilter($regex_replace_filter);

        // Add html_id filter:
        $html_id_filter = new \Twig\TwigFilter('html_id', function ($string) {
            $new_string = '';

            $new_string = $this->htmlID($string);

            return $new_string;
        });
        $twig->addFilter($html_id_filter);

        // Add sum filter:
        $sum_filter = new \Twig\TwigFilter('sum', function ($array) {
            return array_sum($array);
        });
        $twig->addFilter($sum_filter);

        // Add str_replace filter:
        $str_replace = new \Twig\TwigFilter('str_replace', function ($string, $search = '', $replace = '') {
            $new_string = '';

            $new_string = str_replace( $search, $replace, $string);

            return $new_string;
        });
        $twig->addFilter($str_replace);


       // Add filter for image fallback (image to use if preferred one doesn't exist):
        $img_fallback_filter = new \Twig\TwigFilter('fallback', function ($image_path, $fallback_path) {

            $file_headers = @get_headers($image_path);
            if($file_headers[0] != 'HTTP/1.1 404 Not Found') {
                return $image_path;
            }

            $file_headers = @get_headers($fallback_path);
            if($file_headers[0] != 'HTTP/1.1 404 Not Found') {
                return $fallback_path;
            }

            return '';
        });
        $twig->addFilter($img_fallback_filter);

       // Add filter for image path (height from width):
       $img_height_filter = new \Twig\TwigFilter('height', function ($image_path, $width) {

            $image_info = @getimagesize($image_path);

            if (!$image_info) {
                return 'image path not found: ' . $image_path;
            }

            $width = (int) $width;

            if ($image_info[0] > $image_info[1]) {
                $image_ratio = $image_info[0] / $image_info[1];
                $height = round($width / $image_ratio);
            } else {
                $image_ratio = $image_info[1] / $image_info[0];
                $height = round($width * $image_ratio);
            }
            //$height = round($width * $image_ratio);

            return $height;
        });
        $twig->addFilter($img_height_filter);

       // Add filter for image path (width from height):
       $img_width_filter = new \Twig\TwigFilter('width', function ($image_path, $height) {

            $image_info = @getimagesize($image_path);

            if (!$image_info) {
                return 'image path not found: ' . $image_path;
            }

            $height = (int) $height;

            if ($image_info[0] > $image_info[1]) {
                $image_ratio = $image_info[0] / $image_info[1];
                $width = round($height * $image_ratio);
            } else {
                $image_ratio = $image_info[1] / $image_info[0];
                $width = round($height / $image_ratio);
            }
            //$width = round($height / $image_ratio);

            return $width;
        });
        $twig->addFilter($img_width_filter);


        return $twig;
    }*/

    public function getManualMarkers(Registry $config, SiteApplication $app): array
    {
        if (!$app instanceof SiteApplication) {
            return [];
        }
        $markers        = [];
        $manual_markers = $config->get('manual_markers', false);
        // Handle any manual markers:
        if ($manual_markers) {
            // Treat markers as CSV.
            $manual_markers_data = $this->csvArray($manual_markers);
            foreach ($manual_markers_data as $row) {
                $markers[] = array_combine(array('lat', 'lng', 'color', 'popup'), $row);
            }
        }

        return $markers;
    }

    public function getRemoteMarkers(Registry $config, SiteApplication $app): array
    {
        if (!$app instanceof SiteApplication) {
            return [];
        }
        $remote_markers = [];
        $remote_markers_url = $config->get('remote_markers_url', false);
        $json_format    = $config->get('remote_markers_json_format', false);

        // Allow for relative data src URLs:
        if ($remote_markers_url && strpos($remote_markers_url, 'http') !== 0) {
            $s                  = empty($_SERVER['SERVER_PORT']) ? '' : ($_SERVER['SERVER_PORT'] == '443' ? 's' : '');
            $protocol           = preg_replace('#/.*#',  $s, strtolower($_SERVER['SERVER_PROTOCOL']));
            $domain             = $protocol.'://'.$_SERVER['SERVER_NAME'];
            $remote_markers_url = $domain . '/' . trim($remote_markers_url, '/');
        }

        #echo '<pre>'; var_dump($remote_markers); echo '</pre>';exit;
        // Handle any remote markers:
        // Treat markers as CSV.
        if ($remote_markers_url && $remote_markers_data = file_get_contents($remote_markers_url)) {

            // Let's see if we an decode it:
            if ($remote_markers_json = json_decode($remote_markers_data, true)) {
                if (!empty($json_format)) {
                    $twig_data = $remote_markers_json;

                    // We need to parse this to format the json:
                    $loader = new \Twig\Loader\ArrayLoader(array('tpl' => $json_format));
                    $twig   = new \Twig\Environment($loader);

                    // Add html_id filter:
                    $html_id_filter = new \Twig\TwigFilter('html_id', function ($string) {
                        $new_string = '';

                        $new_string = $this->htmlID($string);

                        return $new_string;
                    });
                    $twig->addFilter($html_id_filter);

                    try {
                        $json = $twig->render('tpl', array('data' => $twig_data));
                    } catch (Exception $e) {
                        echo 'Caught exception: ',  $e->getMessage(), "\n";
                    }

                    $remote_markers = json_decode($json, true);
                }
            }
        }

        return $remote_markers;
    }

    /*public function getStuff(Registry $config, SiteApplication $app): array
    {
        if (!$app instanceof SiteApplication) {
            return [];
        }
        $db = $this->getDatabase();

        // Do some database stuff here.

        return ["Hello, world."];
    }*/

}
