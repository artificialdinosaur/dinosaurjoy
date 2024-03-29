<?php
/**
 * Yoast SEO Plugin File.
 *
 * @package Yoast\YoastSEO\Dependency_Injection
 */

namespace Yoast\WP\SEO\Dependency_Injection;

use Symfony\Component\DependencyInjection\Definition;
use Yoast\WP\SEO\Repositories\Indexable_Repository;
use Yoast\WP\SEO\Repositories\Primary_Term_Repository;
use Yoast\WP\SEO\Repositories\SEO_Links_Repository;
use Yoast\WP\SEO\Repositories\SEO_Meta_Repository;
use Yoast\WP\SEO\WordPress\Wrapper;

/* @var $container \Symfony\Component\DependencyInjection\ContainerBuilder */

// 更多精品WP资源尽在喵容：miaoroom.com
//WordPress factory functions.
$container->register( 'wpdb', 'wpdb' )->setFactory( [ Wrapper::class, 'get_wpdb' ] );
$container->register( 'wp_query', 'WP_Query' )->setFactory( [ Wrapper::class, 'get_wp_query' ] );

// 更多精品WP资源尽在喵容：miaoroom.com
//Model repository factory functions.
$container->register( Indexable_Repository::class, Indexable_Repository::class )->setFactory( [ Indexable_Repository::class, 'get_instance' ] )->setAutowired( true );
$container->register( Primary_Term_Repository::class, Primary_Term_Repository::class )->setFactory( [ Primary_Term_Repository::class, 'get_instance' ] )->setAutowired( true );
$container->register( SEO_Meta_Repository::class, SEO_Meta_Repository::class )->setFactory( [ SEO_Meta_Repository::class, 'get_instance' ] )->setAutowired( true );
$container->register( SEO_Links_Repository::class, SEO_Links_Repository::class )->setFactory( [ SEO_Links_Repository::class, 'get_instance' ] )->setAutowired( true );

$excluded_files = [
	'main.php',
];

$excluded_directories = [
	'models',
	'loaders',
	'wordpress',
	'generated',
	'orm',
];

$excluded = \implode( ',', \array_merge( $excluded_directories, $excluded_files ) );

$base_definition = new Definition();

$base_definition
	->setAutowired( true )
	->setAutoconfigured( true )
	->setPublic( false );

/* @var $loader \Yoast\WP\SEO\Dependency_Injection\Custom_Loader */
$loader->registerClasses( $base_definition, 'Yoast\\WP\\SEO\\', 'src/*', 'src/{' . $excluded . '}' );

if ( \file_exists( __DIR__ . '/../../premium/config/dependency-injection/services.php' ) ) {
	include __DIR__ . '/../../premium/config/dependency-injection/services.php';
}
