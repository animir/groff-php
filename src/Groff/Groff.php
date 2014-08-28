<?php
namespace Groff;
/**
 * Wrapper for groff
 *
 * @author animir
 */

class Groff {
    
    private $executable;
    
    private $outFormat = [
        'dvi',
        'html',
        'xhtml',
        'lbp',
        'lj4',
        'ps',
        'pdf'
    ];
    
     /**
     * Setup path to the groff
     *
     * @param string $executable Path to the pandoc executable
     */
    public function __construct($executable = null)
    {
        if ( ! $executable) {
            exec('which groff', $output, $returnVar);
            if ($returnVar === 0) {
                $this->executable = $output[0];
            } else {
                throw new \Exception('Unable to locate groff');
            }
        } else {
            $this->executable = $executable;
        }

        if ( ! is_executable($this->executable)) {
            throw new \Exception('Groff is not executable');
        }
    }

    /**
     * Run the conversion from one type to another
     *
     * 
     * @param string $from The type we are converting from
     * @param string $to   The type we want to convert the document to
     *
     * @return string
     */
    public function convert($string, $to = 'html')
    {
        if ( ! in_array($to, $this->outFormat)) {
            throw new Exception(
                sprintf('%s is not a valid real device for groff. See "man groff"', $to)
            );
        }
        $tmpFile = sprintf("%s/%s", sys_get_temp_dir(), uniqid("groff"));
        file_put_contents($tmpFile, $string);

        $command = sprintf(
            '%s -T%s %s',            
            $this->executable,
            $to,
            $tmpFile
        );

        exec($command, $output);
        unlink($tmpFile);
        return implode("\n", $output);
    }
}
