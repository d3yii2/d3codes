<?php


namespace d3yii2\d3codes\components;


use PhpExec\Exception;
use Yii;
use yii\base\Component;
use yii\helpers\VarDumper;
use yii\web\NotFoundHttpException;
use PhpExec\Command;

/**
 * work only on windows
 * load html page, convert ot PHP and send to windows printer
 *
 * Class PrintWindowsPrinter
 * @package d3yii2\d3codes\components
 */
class PrintWindowsPrinter extends Component
{
    /**
     * @var string server printer name on windows
     */
    public $printerName;

    /**
     * @var string  path to chrome exe. Used for generate PDF
     */
    public $chromeExe;

    /**
     * @var string path to PDFtoPrinter exe fiel
     * @see http://www.columbia.edu/~em36/pdftoprinter.html
     */
    public $PDFtoPrinter;

    /**
     * @param string $url URL return label with barcode
     * @return bool
     * @throws Exception|NotFoundHttpException
     */
    public function print(string $url): bool
    {
        $temPath =escapeshellarg($this->getTempFile('4printer','pdf'));

        if (!$this->exec($this->chromeExe,
            [
                '--headless',
                '--print-to-pdf=' . $temPath,
                '"'.$url.'"'
            ]
        )) {
            return false;
        }
        $result = $this->exec($this->PDFtoPrinter,[
            $temPath,
            '"'.$this->printerName.'"'
        ]);
        sleep(1);
        if(file_exists($temPath)){
            unlink($temPath);
        }
        return $result;

    }

    /**
     * Create a temp file and get full path
     * @param string $prefix (optional) Name prefix
     * @param string $extension
     * @return string Full temp file path
     * @throws NotFoundHttpException When tmp directory doesn't exist or failed to create
     */
    private function getTempFile(string $prefix = 'temp',string $extension = 'tmp'): string
    {
        $tmpDir = Yii::$app->runtimePath . '/tmp';

        if (!is_dir($tmpDir) && (!@mkdir($tmpDir) && !is_dir($tmpDir))) {
            throw new NotFoundHttpException ('temp directory does not exist');
        }

        return preg_replace('#\.tmp$#','.'.$extension,tempnam($tmpDir, $prefix));

    }

    /**
     * @param string $execCommand
     * @param array $arguments
     * @return bool
     * @throws Exception
     */
    public function exec(string $execCommand,array $arguments = []): bool
    {

        $command = new Command($execCommand, $arguments, false);
        $result = $command->run();
        if (!$result->isSuccess()) {
            Yii::error('Exec error: ' . $execCommand);
            Yii::error('Arguments: ' . VarDumper::dumpAsString($arguments));
            Yii::error('Output: ' . $result->getOutput());
            Yii::error('ExitCode: ' . $result->getExitCode());
            Yii::error('ErrorOutput: ' . $result->getErrorOutput());
            return false;
        }
        return true;
    }
}