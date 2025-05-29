<?php

namespace Drupal\generate_vehicles\Drush\Commands;

use Drupal\Core\Entity\EntityTypeManager;
use Drush\Attributes as CLI;
use Drush\Commands\DrushCommands;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\generate_vehicles\Services\GenerateService;

/**
 * A Drush commandfile.
 */
final class GenerateCommands extends DrushCommands {

  /**
   * The entitytypeManager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManager
   */
  protected $entityTypeManager;

  /**
   * The generator.
   *
   * @var \Drupal\generate_vehicles\Services\GenerateService
   */
  protected $generator;

   /**
   * The constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManager $entityTypeManager
   *   The entitytypeManager.
   * @param \Drupal\generate_vehicles\Services\GenerateService $generator
   *   The generator.
   */
  public function __construct(EntityTypeManager $entityTypeManager, GenerateService $generator) {
    $this->entityTypeManager = $entityTypeManager;
     $this->generator = $generator;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('generate_vehicles.generation')
    );
  }

  /**
   * Command description here.
   */
  #[CLI\Command(name: 'generate_vehicles', aliases: ['gv'])]
  #[CLI\Usage(name: 'generate_vehicles (gv)', description: 'Usage description')]
  public function commandName($args = NULL) {

    // If there isn't a number of nodes to generate in command.
    // (ex drush gv)
    if($args == NULL) {

      // Print an error.
      $this->io()->error('Please specify the number of vehicle nodes to generate (ex.drush gv 15).');
      return;
    }

    // If there in a number in command.
    else{
          // The nodes created must be 1-50.
          if($args > 50 || $args == 0){

            // Print a warning.
          $this->io()->warning("You can generate from 1 to 50 vehicle nodes. Please try again.");
        }
          // If the command is writen correctly.
          else{

              // Generate the number .............................
              $numNodes = $this->generator->generateNodesArray($args);

              // 
              foreach($numNodes as $node){

              // Create the node vehicle.
              $this->generator->createNode($node);
                 }
              // Print messages.
              $this->output()->writeln("Hello");
              $this->output()->writeln('You are about to generate ' . $args . ' vehicle nodes');
              $this->io()->confirm('Please confirm the generation');

              // Return a message.
              return $this->io()->success('Successful generation.');
        }
    }

  }


}
