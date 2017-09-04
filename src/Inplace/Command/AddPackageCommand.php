<?php

/*
 * This file is part of inplace/inplace
 *
 *  (c) Scott Wilcox <scott@dor.ky>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 *
 */

namespace Inplace\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;

class AddPackageCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('fetch')
            ->setDescription('Fetch an inplace into the current directory.')
            ->addArgument(
                'packages',
                InputArgument::IS_ARRAY,
                'Name of the Github repos to inplace into the current directory'
            );
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $packages = $input->getArgument('packages');
        $pwd = getcwd();

        if (count($packages) === 0) {
            $output->writeln('<error> ❌  No packages provided to fetch.</error>');
            exit();
        }

        // Make our list of packages to fetch unique
        $packages = array_unique($packages);

        // Inform the user
        $output->writeln('<info> 🌟  Found a total of ' . number_format(count($packages)) . ' package(s) to fetch:</info>');
        $output->writeln('');

        // Iterate the packages and try to inplace the files
        foreach ($packages as $package) {

            // Build a temporary directory name
            $tmp_dir = sys_get_temp_dir() . "/" . md5(time());
            mkdir($tmp_dir);
            $inplace_path = $tmp_dir . "/.inplace.yml";

            // If we were unable to create a temporary directory, bail out
            if (!file_exists($tmp_dir)) {
                $output->writeln('<error> ❌  Unable to create temporary directory to work in.</error>');
                exit();
            }

            // Clone the repo into our temporary directory
            $git = new \PHPGit\Git();
            $git->clone($package, $tmp_dir);

            // Check we have a valid '.inplace.yml' file present
            if (!file_exists($inplace_path)) {
                $output->writeln('<error> ❌  Skipping project: ' . $package . ' as repo does not contain a .inplace.yml file.</error>');
                continue;
            }

            try {
                $yaml = Yaml::parse(file_get_contents($inplace_path));

                if ((!array_key_exists("files", $yaml) || count($yaml["files"]) === 0)) {
                    $output->writeln('<error> ❌  No files listed to place in .inplace.yml for package: ' . $package . ', skipping</error>');
                } else {
                    foreach ($yaml["files"] as $file) {
                        $this->recursiveCopy($tmp_dir . "/" . $file, $pwd ."/". $file);
                        $output->writeln('<fg=green> 📁  Placed ' . $file . ' into current directory from package: ' . $package . '</>');
                    }
                }
            } catch (ParseException $e) {
                $output->writeln('<error> ❌  Unable to parse .inplace.yml for : ' . $package . ', skipping</error>');
            }

            // Cleanup after ourselves
            $this->removeItems($tmp_dir);
        }


        $output->writeln('');
        $output->writeln('<comment> ✅  Finished running inplace!</comment>');
        $output->writeln('<comment> ✅  Please report bugs, suggestions and ideas to https://github.com/ssx/inplace</comment>');
    }

    private function removeItems($path)
    {
        $fs = new Filesystem();
        try {
            $fs->remove($path);
        } catch (IOExceptionInterface $e) {
            die("An error occurred while removing a directory: " . $e->getPath() . ", unclean shutdown.");
        }
    }

    private function recursiveCopy($source, $dest)
    {
        if (is_dir($source)) {
            $dir_handle = opendir($source);
            while ($file = readdir($dir_handle)) {
                if ($file != "." && $file != "..") {
                    if (is_dir($source . "/" . $file)) {
                        if (!is_dir($dest . "/" . $file)) {
                            mkdir($dest . "/" . $file);
                        }
                        $this->recursiveCopy($source . "/" . $file, $dest . "/" . $file);
                    } else {
                        copy($source . "/" . $file, $dest . "/" . $file);
                    }
                }
            }
            closedir($dir_handle);
        } else {
            copy($source, $dest);
        }
    }
}
