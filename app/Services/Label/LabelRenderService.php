<?php

namespace App\Services\Label;

use Mpdf\Mpdf;
use App\Models\Label;
use Illuminate\Support\Facades\File;

class LabelRenderService
{
    private function assetsPath(string $file): string
    {
        // Assetek: resources/assets/label
        return base_path('resources/assets/label/' . ltrim($file, '/'));
    }

    private function outputFolder(): string
    {
        // Legacy: /upload/label; itt: storage/app/label
        return storage_path('app/label');
    }

    public function calcClass(float|int|string $d, int $number = 0, int $type = 1): string|int
    {
        $d = (float) str_replace(',', '.', (string) $d);

        if ($type === 1) {
            if ($d >= 150) return $number === 0 ? "A+++" : 1;
            if ($d >= 125 && $d < 150) return $number === 0 ? "A++" : 2;
            if ($d >= 98  && $d < 125) return $number === 0 ? "A+" : 3;
            if ($d >= 90  && $d < 98)  return $number === 0 ? "A" : 4;
            if ($d >= 82  && $d < 90)  return $number === 0 ? "B" : 5;
            if ($d >= 75  && $d < 82)  return $number === 0 ? "C" : 6;
            if ($d >= 36  && $d < 75)  return $number === 0 ? "D" : 7;
            if ($d >= 34  && $d < 36)  return $number === 0 ? "E" : 8;
            if ($d >= 30  && $d < 34)  return $number === 0 ? "F" : 9;
            if ($d < 30)               return $number === 0 ? "G" : 10;
        }

        if ($type === 2) {
            if ($d >= 130) return $number === 0 ? "A++" : 1;
            if ($d >= 107 && $d < 130) return $number === 0 ? "A+" : 3;
            if ($d >= 88  && $d < 107) return $number === 0 ? "A" : 4;
            if ($d >= 82  && $d < 88)  return $number === 0 ? "B" : 5;
            if ($d >= 77  && $d < 82)  return $number === 0 ? "C" : 6;
            if ($d >= 72  && $d < 77)  return $number === 0 ? "D" : 7;
            if ($d >= 62  && $d < 72)  return $number === 0 ? "E" : 8;
            if ($d >= 42  && $d < 62)  return $number === 0 ? "F" : 9;
            if ($d < 42)               return $number === 0 ? "G" : 10;
        }

        return $number === 0 ? "" : 0;
    }

    private function calcX(int $fontSize, string $text, string $fontPath): int
    {
        $dimensions = imagettfbbox($fontSize, 0, $fontPath, $text);
        return abs($dimensions[2]);
    }

    private function ensureOutputFolder(): void
    {
        $dir = $this->outputFolder();
        if (!File::exists($dir)) {
            File::makeDirectory($dir, 0755, true);
        }
    }

    /**
     * Legacy: get_image_page
     * Visszaadja a "page" PNG binary-t.
     */
    private function buildImagePage(string $labelPngPath, int $type = 1): string
    {
        // output buffer: imagepng() -> string
        ob_start();

        header('Content-Type: image/png');

        if ($type === 2) {
            $w  = 827;
            $h  = 1169;
            $pw = 2480;
            $ph = 3508;

            $nh = $ph / 2;
            $nw = $pw / 2;

            $im = imagecreatefrompng($labelPngPath);
            $page = imagecreatetruecolor($pw, $ph);
            imagefill($page, 0, 0, imagecolorallocate($page, 255, 255, 255));

            imagecopyresampled($page, $im, 0,   0,   0, 0, $nw, $nh, $w, $h);
            imagecopyresampled($page, $im, $nw, 0,   0, 0, $nw, $nh, $w, $h);
            imagecopyresampled($page, $im, 0,   $nh, 0, 0, $nw, $nh, $w, $h);
            imagecopyresampled($page, $im, $nw, $nh, 0, 0, $nw, $nh, $w, $h);

            imagepng($page);
            imagedestroy($page);
            imagedestroy($im);
        } else {
            $w  = 1240;
            $h  = 2362;
            $pw = 2480;
            $ph = 1748;

            $nh = $h * ($ph / $h);
            $nw = $w * ($ph / $h);

            $im = imagecreatefrompng($labelPngPath);
            $page = imagecreatetruecolor($pw, $ph);
            imagefill($page, 0, 0, imagecolorallocate($page, 255, 255, 255));

            imagecopyresampled($page, $im, $pw / 4 - $nw / 2, 0, 0, 0, $nw, $nh, $w, $h);
            imagecopyresampled($page, $im, $pw * 0.75 - $nw / 2, 0, 0, 0, $nw, $nh, $w, $h);

            imagepng($page);
            imagedestroy($page);
            imagedestroy($im);
        }

        return (string) ob_get_clean();
    }

    /**
     * Legacy get_img_1: "Címke"
     * return: page PNG binary
     */
    public function renderImg1(Label $label): string
    {
        $this->ensureOutputFolder();

        $font  = $this->assetsPath('calibri.ttf');
        $fontb = $this->assetsPath('calibrib.ttf');

        $d = $label->decoded_data;

        $filePath = $this->outputFolder() . DIRECTORY_SEPARATOR . 'label_' . $label->id . '.png';

        if ((int)$label->type === 1) {
            $bg = $this->assetsPath('label_1.png');
            $im = imagecreatefrompng($bg);
            $black = imagecolorallocate($im, 0, 0, 0);

            imagettftext($im, 35, 0, 115, 420, $black, $font,  (string)($d['a1'] ?? ''));
            imagettftext($im, 35, 0, 1130 - $this->calcX(35, (string)($d['a2'] ?? ''), $font), 420, $black, $font, (string)($d['a2'] ?? ''));

            imagettftext($im, 50, 0, 270 - $this->calcX(50, (string)($d['a4'] ?? ''), $fontb), 1918, $black, $fontb, (string)($d['a4'] ?? ''));
            imagettftext($im, 100,0, 790 - $this->calcX(100,(string)($d['a5'] ?? ''), $fontb), 1850, $black, $fontb, (string)($d['a5'] ?? ''));

            $q = (int) $this->calcClass($d['a3'] ?? 0, 1, 1);
            $signal = $this->assetsPath('q_' . $q . '.png');
            $im2 = imagecreatefrompng($signal);

            imagecopyresampled($im, $im2, 865, 760 - (142 / 2) + (($q - 2) * 73), 0, 0, 262, 142, 262, 142);

            imagepng($im, $filePath);
            imagedestroy($im);
            imagedestroy($im2);

            return $this->buildImagePage($filePath, 1);
        }

        if ((int)$label->type === 2) {
            $bg = $this->assetsPath('label_2.png');
            $im = imagecreatefrompng($bg);
            $black = imagecolorallocate($im, 0, 0, 0);

            imagettftext($im, 35, 0, 115, 420, $black, $font, (string)($d['a1'] ?? ''));
            imagettftext($im, 35, 0, 1130 - $this->calcX(35, (string)($d['a2'] ?? ''), $font), 420, $black, $font, (string)($d['a2'] ?? ''));

            imagettftext($im, 100, 0, 905 - ($this->calcX(100, (string)($d['a4'] ?? ''), $fontb) / 2), 1535, $black, $fontb, (string)($d['a4'] ?? ''));

            if (!empty($d['a5'])) {
                $l = $this->assetsPath('label_2_2.png');
                $im2 = imagecreatefrompng($l);
                imagecopyresampled($im, $im2, 106, 1729, 0, 0, 1023, 370, 1023, 370);
                imagedestroy($im2);

                imagettftext($im, 100, 0, 905 - ($this->calcX(100, (string)($d['a5'] ?? ''), $fontb) / 2), 1925, $black, $fontb, (string)($d['a5'] ?? ''));
            }

            $q = (int) $this->calcClass($d['a3'] ?? 0, 1, 2);
            $signal = $this->assetsPath('q_' . $q . '.png');
            $im2 = imagecreatefrompng($signal);
            imagecopyresampled($im, $im2, 865, 600 - (142 / 2) + (($q - 2) * 73), 0, 0, 262, 142, 262, 142);

            imagepng($im, $filePath);
            imagedestroy($im);
            imagedestroy($im2);

            return $this->buildImagePage($filePath, 1);
        }

        return '';
    }

    /**
     * Legacy get_img_2: "Adattábla"
     * return: page PNG binary
     */
    public function renderImg2(Label $label): string
    {
        $this->ensureOutputFolder();

        $font = $this->assetsPath('calibri.ttf');
        $d = $label->decoded_data;

        $filePath = $this->outputFolder() . DIRECTORY_SEPARATOR . 'label_' . $label->id . '.png';

        if ((int)$label->type === 1) {
            $bg = $this->assetsPath('label_3.png');
            $im = imagecreatefrompng($bg);
            $black = imagecolorallocate($im, 0, 0, 0);

            imagettftext($im, 35, 0, 750 - $this->calcX(35, (string)($d['b1'] ?? ''), $font), 150, $black, $font, (string)($d['b1'] ?? ''));
            imagettftext($im, 50, 0, 640, 340, $black, $font, (string)$this->calcClass($d['a3'] ?? 0, 0, 1));
            imagettftext($im, 35, 0, 695 - $this->calcX(35, (string)($d['a5'] ?? ''), $font), 492, $black, $font, (string)($d['a5'] ?? ''));
            imagettftext($im, 35, 0, 695 - $this->calcX(35, (string)($d['a3'] ?? ''), $font), 629, $black, $font, (string)($d['a3'] ?? ''));
            imagettftext($im, 35, 0, 695 - $this->calcX(35, (string)($d['b5'] ?? ''), $font), 770, $black, $font, (string)($d['b5'] ?? ''));
            imagettftext($im, 35, 0, 695 - $this->calcX(35, (string)($d['a4'] ?? ''), $font), 910, $black, $font, (string)($d['a4'] ?? ''));
            imagettftext($im, 20, 0, 70, 1040, $black, $font, (string)($d['b7'] ?? ''));

            imagepng($im, $filePath);
            imagedestroy($im);

            return $this->buildImagePage($filePath, 2);
        }

        if ((int)$label->type === 2) {
            $bg = $this->assetsPath('label_4_v2.png');
            $im = imagecreatefrompng($bg);
            $black = imagecolorallocate($im, 0, 0, 0);

            imagettftext($im, 35, 0, 750 - $this->calcX(35, (string)($d['b1'] ?? ''), $font), 150, $black, $font, (string)($d['b1'] ?? ''));
            imagettftext($im, 50, 0, 640, 340, $black, $font, (string)$this->calcClass($d['a3'] ?? 0, 0, 2));

            imagettftext($im, 35, 0, 658 - $this->calcX(35, (string)($d['a4'] ?? ''), $font), 433, $black, $font, (string)($d['a4'] ?? ''));
            imagettftext($im, 35, 0, 658 - $this->calcX(35, (string)($d['a5'] ?? ''), $font), 532, $black, $font, (string)($d['a5'] ?? ''));
            imagettftext($im, 35, 0, 658 - $this->calcX(35, (string)($d['a3'] ?? ''), $font), 625, $black, $font, (string)($d['a3'] ?? ''));

            imagettftext($im, 35, 0, 658 - $this->calcX(35, (string)($d['b3'] ?? ''), $font), 750, $black, $font, (string)($d['b3'] ?? ''));
            imagettftext($im, 35, 0, 658 - $this->calcX(35, (string)($d['b3'] ?? ''), $font), 845, $black, $font, (string)($d['b6'] ?? ''));
            imagettftext($im, 35, 0, 658 - $this->calcX(35, (string)($d['b3'] ?? ''), $font), 918, $black, $font, (string)($d['b5'] ?? ''));

            imagettftext($im, 20, 0, 70, 1040, $black, $font, (string)($d['b4'] ?? ''));

            imagepng($im, $filePath);
            imagedestroy($im);

            return $this->buildImagePage($filePath, 2);
        }

        return '';
    }
    public function renderPdf(Label $label): string
    {
        $data = $label->decoded_data; // legacy: $data tömböt várnak a template-ek

        $template = ((int) $label->type === 2)
            ? 'pdf_template_2.php'
            : 'pdf_template_1.php';

        $templatePath = storage_path('app/legacy/label/' . $template);
        $footerPath   = storage_path('app/legacy/label/pdf_template_footer.php');

        if (!File::exists($templatePath)) {
            throw new \RuntimeException("Legacy PDF template not found: {$templatePath}");
        }
        if (!File::exists($footerPath)) {
            throw new \RuntimeException("Legacy PDF footer not found: {$footerPath}");
        }

        // 1) Legacy PHP template futtatás short_open_tag kompatibilisen (fájl módosítása nélkül)
        $htmlBody = $this->renderLegacyPhpFileShortTagSafe($templatePath, [
            'data'  => $data,
            'label' => $label,
        ]);

        $htmlFooter = $this->renderLegacyPhpFileShortTagSafe($footerPath, [
            'data'  => $data,
            'label' => $label,
        ]);

        // 2) mPDF
        $mpdf = new Mpdf([
            'mode'    => 'utf-8',
            'format'  => 'A4',
            'tempDir' => storage_path('app/mpdf-temp'),
        ]);

        // hogy a footerben lévő: <img src="static/img/..."> működjön
        $mpdf->SetBasePath(public_path());

        // A template-ben van teljes HTML struktúra és a footer is zárhat body/html-t,
        // ezért mi egyszerűen sorban adjuk oda.
        $mpdf->WriteHTML($htmlBody);
        $mpdf->WriteHTML($htmlFooter);

        return $mpdf->Output('', 'S'); // PDF binary string
    }
    private function renderLegacyPhpFileShortTagSafe(string $path, array $vars): string
    {
        $php = File::get($path);

        // Short open tag kompatibilitás:
        // - "<? " -> "<?php " (kivéve a "<?= " esetet, azt meghagyjuk)
        // - "<?\t" / "<?\n" stb. is kezelve
        $php = preg_replace('/<\?(?!\=)/', '<?php', $php);

        // Biztonságos izolált futtatás:
        return $this->evalPhpToString($php, $vars);
    }

    private function evalPhpToString(string $php, array $vars): string
    {
        extract($vars, EXTR_SKIP);

        ob_start();
        // PHP block-ként futtatjuk
        eval('?>' . $php);
        return (string) ob_get_clean();
    }
    private function renderLegacyPhpTemplate(string $path, array $vars): string
    {
        extract($vars, EXTR_SKIP);

        ob_start();
        include $path;
        return (string) ob_get_clean();
    }
}
