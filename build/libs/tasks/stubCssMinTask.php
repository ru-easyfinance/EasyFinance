<?php
/**
 * Task to minimy css files.
 *
 * Port of Frank Kleine <mikey@stubbles.net> stubJsMinTask for use with cssmin (http://code.google.com/p/cssmin/)
 *
 * @author      Statsenko Vladinir <vova_33@gala.net>
 * @link http://www.simplecoding.org
 */
/**
 * Uses the Phing Task
 */
require_once 'phing/Task.php';
/**
 * Task to minimy css files.
 */
class stubCssMinTask extends Task
{
    /**
     * path to CSSmin
     *
     * @var  string
     */
    protected $cssMinPath;
    /**
     * the source files
     *
     * @var  FileSet
     */
    protected $filesets    = array();
    /**
     * Whether the build should fail, if
     * errors occured
     *
     * @var boolean
     */
    protected $failonerror = false;
    /**
     * directory to put minified CSS files into
     *
     * @var  string
     */
    protected $targetDir;

    /**
     * sets the path where CSSmin can be found
     *
     * @param  string  $cssMinPath
     */
    public function setCssMinPath($cssMinPath)
    {
        $this->cssMinPath = $cssMinPath;
    }

    /**
     *  Nested creator, adds a set of files (nested fileset attribute).
     */
    public function createFileSet()
    {
        $num = array_push($this->filesets, new FileSet());
        return $this->filesets[$num - 1];
    }

    /**
     * Whether the build should fail, if an error occured.
     *
     * @param boolean $value
     */
    public function setFailonerror($value)
    {
        $this->failonerror = $value;
    }

    /**
     * sets the directory where minified javascript files should be put inot
     *
     * @param  string  $targetDir
     */
    public function setTargetDir($targetDir)
    {
        $this->targetDir = $targetDir;
    }

    /**
     * The init method: Do init steps.
     */
    public function init()
    {
        return true;
    }

    /**
     * The main entry point method.
     */
    public function main()
    {
        if (class_exists('cssmin', false) == false) {
	            require_once $this->cssMinPath;
	        }
	        
	        foreach ($this->filesets as $fs) {
	            try {
	                $files    = $fs->getDirectoryScanner($this->project)->getIncludedFiles();
	                $fullPath = realpath($fs->getDir($this->project));
	                foreach ($files as $file) {
	                    $this->log('Minifying file ' . $file);
						$target = $this->targetDir . '/' . str_replace($fullPath, '', str_replace('.css', '-min.css', $file));
						if (file_exists(dirname($target)) == false) {
							mkdir(dirname($target), 0700, true);
						}
						
						file_put_contents($target, cssmin::minify(file_get_contents($fullPath . '/' . $file), "remove-last-semicolon,preserve-urls"));
	                }
	            } catch (BuildException $be) {
	                // directory doesn't exist or is not readable
	                if ($this->failonerror) {
	                throw $be;
                } else {
                    $this->log($be->getMessage(), $this->quiet ? Project::MSG_VERBOSE : Project::MSG_WARN);
                }
            }
        }
    }
}
?>