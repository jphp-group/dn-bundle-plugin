<?php

use packager\{
    cli\Console, Event, JavaExec, Packager, Vendor
};
use compress\ZipArchive;
use php\io\File;
use php\lib\str;
use php\util\Configuration;
use php\lib\fs;
use compress\ZipArchiveEntry;
use php\io\Stream;
use php\lib\arr;
use php\io\MiscStream;
use compress\ZipArchiveInput;


/**
 * Class DevelNextBundle
 * @jppm-task-prefix bundle
 *
 * @jppm-task build as build
 */
class DevelNextBundle
{
    /**
     * @jppm-need-package
     * @jppm-description Build project and create dnbundle.
     * @param Event $event
     */
    public function build(Event $e){
        Tasks::run('build', []);

        $buildFileName = "{$e->package()->getName()}-{$e->package()->getVersion('last')}";

        Tasks::createDir('bundle');
        Tasks::cleanDir('bundle');

        $resourceConf = new Configuration();
        $resourceConf->put($e->package()->getAny('develnext-bundle'));
        $resourceConf->save("bundle/.resource");


        Tasks::createFile("bundle/dn-{$e->package()->getName()}-bundle.jar");
        $bundle = new ZipArchive("bundle/dn-{$e->package()->getName()}-bundle.jar");
        $bundle->open();
        fs::scan("src-bundle", function(File $file)use($bundle){
            if($file->isFile()){
                $bundle->addFile($file, fs::relativize($file, "src-bundle"));
            }
        });
        $bundle->close();

        Tasks::createFile("bundle/{$buildFileName}.dnbundle");

        $out = new ZipArchive("bundle/{$buildFileName}.dnbundle");
        $out->open();
        $out->addFile("bundle/dn-{$e->package()->getName()}-bundle.jar", "bundle/dn-{$e->package()->getName()}-bundle.jar");
        $out->addFile("bundle/.resource", ".resource");

        /** @var File $extFile */
        $extFile = null;

        fs::scan('jars', function(File $file)use($out,&$extFile){
            if(!$extFile) {
                $jar = new ZipArchive($file);

                $entry = $jar->read('META-INF/services/php.runtime.ext.support.Extension');
                if($entry){
                    $extFile = $file;
                    return;
                }

            }
            $out->addFile($file, "bundle/{$file->getName()}");
        });
        if($extFile){
            Tasks::createDir("bundle/ext");
            $input = new ZipArchiveInput($extFile);
            while($entry = $input->nextEntry()){
                if($input->canReadEntryData($entry) && !$entry->isDirectory()){
                    $name = "bundle/ext/{$entry->name}";
                    fs::ensureParent($name);
                    fs::copy($input->stream(), $name);
                }
            }

            Tasks::copy("sdk", "bundle/ext/JPHP-INF/sdk");
            $jar = new ZipArchive("bundle/{$extFile->getName()}");
            $jar->open();
            fs::scan("bundle/ext", function(File $file)use($jar){
                if($file->isFile()) {
                    $jar->addFile($file, fs::relativize($file, "bundle/ext"));
                }
            });
            $jar->close();
            $out->addFile("bundle/{$extFile->getName()}", "bundle/{$extFile->getName()}");
        }
        $out->close();

        Tasks::deleteFile("bundle/.resource", true);
        if($extFile){
            Tasks::deleteFile("bundle/{$extFile->getName()}", true);
        }
        Tasks::deleteFile("bundle/dn-{$e->package()->getName()}-bundle.jar", true);
        Tasks::cleanDir("bundleext", [], true);
        Tasks::deleteFile("bundle/ext", true);

        Console::log("You can find bundle in ".fs::abs("bundle/{$buildFileName}.dnbundle"));
    }
}