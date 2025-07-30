<?php

namespace DrupalProject\composer;

use Composer\Script\Event;
use DrupalFinder\DrupalFinderComposerRuntime;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Helper Composer scripts.
 */
class ScriptHandler {

  /**
   * Generate default files, required for a minimal Drupal install.
   *
   * @param \Composer\Script\Event $event
   *   The composer event.
   */
  public static function createRequiredFiles(Event $event) {
    $fs = new Filesystem();
    $drupalFinder = new DrupalFinderComposerRuntime();
    $drupalRoot = $drupalFinder->getDrupalRoot();
    $composerRoot = $drupalFinder->getComposerRoot();

    $dirs = [
      'modules',
      'profiles',
      'themes',
    ];

    // Required for unit testing.
    foreach ($dirs as $dir) {
      if (!$fs->exists($drupalRoot . '/' . $dir)) {
        $fs->mkdir($drupalRoot . '/' . $dir);
        $fs->touch($drupalRoot . '/' . $dir . '/.gitkeep');
      }
    }

    // Prepare dotenv file.
    if (!$fs->exists($composerRoot . '/.env') && $fs->exists($composerRoot . '/.env.example')) {
      $fs->copy($composerRoot . '/.env.example', $composerRoot . '/.env');
    }

    // Prepare salt before cooking.
    if (!$fs->exists($drupalRoot . '/sites/default/salt.txt')) {
      $randomSalt = substr(md5(random_int(PHP_INT_MIN, PHP_INT_MAX)), 0, 20);
      $fs->dumpFile($drupalRoot . '/sites/default/salt.txt', $randomSalt);
    }

    // Prepare the settings file for installation.
    if (!$fs->exists($drupalRoot . '/sites/default/settings.php') && $fs->exists($drupalRoot . '/sites/custom.settings.php')) {
      $fs->copy($drupalRoot . '/sites/custom.settings.php', $drupalRoot . '/sites/default/settings.php');
      $fs->chmod($drupalRoot . '/sites/default/settings.php', 0666);
      $event->getIO()->write("Created a sites/default/settings.php file from custom file with chmod 0666");
    }
    if (!$fs->exists($drupalRoot . '/sites/default/settings.php') && $fs->exists($drupalRoot . '/sites/default/default.settings.php')) {
      $fs->copy($drupalRoot . '/sites/default/default.settings.php', $drupalRoot . '/sites/default/settings.php');
      $fs->chmod($drupalRoot . '/sites/default/settings.php', 0666);
      $event->getIO()->write("Created a sites/default/settings.php file from default file, with chmod 0666");
    }

    // Prepare the services file for development.
    if (!$fs->exists($drupalRoot . '/sites/default/services.yml') && $fs->exists($drupalRoot . '/sites/custom.services.yml')) {
      $fs->copy($drupalRoot . '/sites/custom.services.yml', $drupalRoot . '/sites/default/services.yml');
      $event->getIO()->write("Created a sites/default/services.yml file from custom file");
    }
    if (!$fs->exists($drupalRoot . '/sites/default/services.yml') && $fs->exists($drupalRoot . '/sites/default.services.yml')) {
      $fs->copy($drupalRoot . '/sites/default/default.services.yml', $drupalRoot . '/sites/default/services.yml');
      $event->getIO()->write("Created a sites/default/services.yml file from custom file");
    }

    // Create the files directory with chmod 0777.
    if (!$fs->exists($drupalRoot . '/sites/default/files')) {
      $oldmask = umask(0);
      $fs->mkdir($drupalRoot . '/sites/default/files', 0777);
      umask($oldmask);
      $event->getIO()->write("Created a sites/default/files directory with chmod 0777");
    }
  }

}
