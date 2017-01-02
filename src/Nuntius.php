<?php

namespace Nuntius;

class Nuntius {

  /**
   * The path of the plugins.
   *
   * @var NuntiusPluginAbstract[]
   */
  protected $plugins;

  /**
   * @param NuntiusPluginAbstract $plugin
   * @return $this
   */
  public function addPlugins(NuntiusPluginAbstract $plugin) {
    $this->plugins[] = $plugin;

    return $this;
  }

  /**
   * Get the best matching plugin accodring to the current text.
   *
   * @param $sentence
   *   The text the user submitted.
   */
  public function getPlugin($sentence) {

    // Remove nuntius mention from the sentence.
    $sentence = trim(str_replace('@nuntius', '', $sentence));

    foreach ($this->plugins as $plugin) {

      foreach ($plugin->formats as $format => $info) {
        if (!$matches = $this->stepDefinitionMatch($sentence, $format)) {
          continue;
        }

        call_user_func_array([$plugin, $info['callback']], $matches);
      }
    }
  }

  /**
   * Check if the plugin is matching.
   *
   * @param $user_input
   *   The text the user submitted.
   * @param $plugin_format
   *   The format of the plugin.
   *
   * @return boolean|array
   *   In case there is not match, return FALSE. If found, return the artguments
   *   from the sentence.
   */
  public function stepDefinitionMatch($user_input, $plugin_format) {
    if (!preg_match($plugin_format, $user_input, $matches)) {
      return FALSE;
    }

    unset($matches[0]);

    return $matches;
  }

}
