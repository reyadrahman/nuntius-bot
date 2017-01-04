<?php

namespace Nuntius;

use GuzzleHttp\Client;
use PhpSlackBot\Command\BaseCommand;

class NuntiusSuperCommand extends BaseCommand {

  /**
   * Nuntius instance.
   *
   * @var Nuntius
   */
  protected $nuntius;

  /**
   * Setting the nuntius object with the plugins.
   *
   * @param Nuntius $nuntius
   *   The nuntius object.
   *
   * @return NuntiusSuperCommand
   *   The current instance.
   */
  public function setNuntius(Nuntius $nuntius) {
    $this->nuntius = $nuntius;

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  protected function configure() {
  }

  /**
   * {@inheritdoc}
   */
  protected function execute($data, $context) {

    list($author, $message) = explode(': ', $data['content']);

    // Get the username from the data.
    $data['username'] = $author;

    // Log all the stuff. For debugging and records.
    Nuntius::getRethinkDB()->addEntry('logs', $data);

    // Get the matching plugin.
    $this->nuntius
      ->setAuthor($author)
      ->getPlugin($message);

    // todo: Move to plugin actions.
    if ($data['type'] == 'presence_change' && $data['presence'] == 'active') {

      // The user logged in. Any stuff we need to tell him?
      $results = Nuntius::getRethinkDB()->getTable('reminders')
        ->filter(\r\row('to')->eq($author))
        ->run(Nuntius::getRethinkDB()->getConnection());

      foreach ($results as $result) {
        $reminder = $result['author'] . ' told me to tell you ' . $result['remind'];
        $this->send($this->getIMChannel($this->getIdFromUserName($result['to'])), $result['to'], $reminder);

        // The reminder no longer have any purpose. Delete it.
        Nuntius::getRethinkDB()->getTable('reminders')
          ->get($result['id'])
          ->delete()
          ->run(Nuntius::getRethinkDB()->getConnection());
      }
    }

  }

  /**
   * Get the ID if a user from the username.
   *
   * @param $username
   *   The username. Usually author.
   *
   * @return string
   *   The ID of the user.
   */
  public function getIdFromUserName($username) {
    foreach ($this->getCurrentContext()['users'] as $user) {
      if ($user['name'] == $username) {
        return $user['id'];
      }
    }
  }

  /**
   * Get the ID of an IM channel between nuntius and the user.
   *
   * @param $userId
   *   The user ID.
   *
   * @return string
   *   The ID of the user.
   */
  public function getIMChannel($userId) {
    $client = new Client();
    $url = 'https://slack.com/api/im.open?';
    $url.= 'token=' . Nuntius::getSettings()['bot_id'];
    $url.= '&user=' . $userId;
    $res = $client->request('GET', $url);
    $obj = json_decode($res->getBody(), true);

    if (isset($obj['channel']['id'])) {
      return $obj['channel']['id'];
    }
  }

  public function nuntiusSendMessage($channel, $subtitle, $text) {
    $this->send($channel, $subtitle, $text);
  }

}
