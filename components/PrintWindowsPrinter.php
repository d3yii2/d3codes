<?php


namespace d3yii2\d3codes\components;


use creocoder\flysystem\FtpFilesystem;
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
     * @param int $copies
     * @return bool
     * @throws Exception
     * @throws NotFoundHttpException
     */
    public function print(string $url, int $copies = 1): bool
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
        sleep(1);
        $result = $this->exec($this->PDFtoPrinter,[
            $temPath,
            '"'.$this->printerName.'" copies=' . $copies
        ]);

        if(file_exists($temPath)){
            unlink($temPath);
        }
        return $result;

    }


    /**
     * @param string $url
     * @param int $copies
     * @return bool
     * @throws Exception
     * @throws NotFoundHttpException
     * @throws \yii\base\Exception
     *
     * @todo papildus parami: host, mode (pasive/active), user, password, timeout sec, debug
     */
    public function printToFtpFilesystem(string $filepath, int $copies = 1): bool
    {
        echo 'a';
        if(!file_exists($filepath)){
            throw new \yii\base\Exception('Neeksite fails: ' . $filepath);
        }
        $copyToFile = basename($filepath,'.pdf');
        $conn_id = ftp_connect('192.168.15.22');
        if(!$login_result = ftp_login($conn_id, 'anonymous', 'anonymous@domain.com')){
            echo print_r($login_result);
        }
        //if(!ftp_pasv($conn_id, true)) echo '"can not switch passive mode"';
        ftp_set_option($conn_id, FTP_TIMEOUT_SEC, 10);
        echo 'd';
        if ((!$conn_id) || (!$login_result)) {
            throw new \yii\base\Exception("FTP connection has failed!");
        }
        $i = 1;
        while($i<=$copies) {
            echo 'f';
            echo '"' . $copyToFile . $i . '.pdf"';

            if(!@ftp_put($conn_id, $copyToFile . $i . '.pdf', $filepath, FTP_BINARY)){
                throw new \yii\base\Exception("can not ftp_put! " . VarDumper::dumpAsString(error_get_last()));
            }
            echo 'g';
            $i++;
        }

//        if(file_exists($filepath)){
//            unlink($filepath);
//        }
        return true;

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
        $tmpDir = Yii::getAlias('@runtime/tmp');
        if (!is_dir($tmpDir) && (!@mkdir($tmpDir) && !is_dir($tmpDir))) {
            throw new NotFoundHttpException ('temp directory does not exist: ' . $tmpDir);
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

        $command = new Command($execCommand, $arguments);
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