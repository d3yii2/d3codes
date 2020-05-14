<?php


namespace d3yii2\d3codes\components;


use Yii;
use yii\base\Component;
use yii\web\NotFoundHttpException;

class PrintWindowsPrinter extends Component
{
    /** @var string */
    public $printerName;

    /** @var string */
    public $chromeExe;

    /**
     * @param string $url
     */
    public function print(string $url)
    {
        $temPath = $this->getTempFile();
        $toPdfCommand = '"' . $this->chromeExe . '"  --headless --print-to-pdf="'.$temPath.'"';
//        $command = new Tomas('ls');
        exec($toPdfCommand.' 2>&1', $output, $return);
        $printCommand = 'PDFtoPrinter "'.$temPath.'"';
        exec($printCommand.' 2>&1', $output, $return);
    }

    /**
     * Create a temp file and get full path
     * @param string $prefix (optional) Name prefix
     * @return string Full temp file path
     * @throws NotFoundHttpException When tmp directory doesn't exist or failed to create
     */
    private function getTempFile(string $prefix = 'temp' ) {
        $tmpDir = Yii::$app->runtimePath.'/tmp';

        if ( !is_dir($tmpDir) && (!@mkdir($tmpDir) && !is_dir($tmpDir)) ) {
            throw new NotFoundHttpException ('temp directory does not exist');
        }

        return tempnam( $tmpDir, $prefix );
    }
}