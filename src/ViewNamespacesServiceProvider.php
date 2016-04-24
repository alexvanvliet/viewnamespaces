<?php

namespace AlexVanVliet\ViewNamespaces;

use Illuminate\Database\DatabaseManager;
use Illuminate\Support\ServiceProvider;
use Illuminate\View\Factory;

class ViewNamespacesServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @param Factory $view The vue factory
     */
    public function boot(Factory $view, DatabaseManager $db)
    {
        $this->publishes([
            __DIR__.'/config' => base_path('config')
        ], 'alexvanvliet-viewnamespaces-config');
        $this->publishes([
            __DIR__.'/views' => base_path('resources/views')
        ], 'alexvanvliet-viewnamespaces-views');

        // Si la config existe
        if ($namespaces = config('viewnamespaces.namespaces')) {
            // On parcourt tous les namespaces
            foreach ($namespaces as $namespace => $options){
                // Si c'est bien un tableau
                if (is_array($options)) {
                    // Si le chemin par défaut existe
                    if (isset($options['default']) && is_string($options['default'])) {
                        // S'il y a une définition dynamique du chemin
                        if (isset($options['dynamic']) && is_array($options['dynamic'])) {
                            $dynamic = $options['dynamic'];
                            // Si on a bien le type de la définition dynamique
                            if (isset($dynamic['type']) && is_string($dynamic['type'])) {
                                $added = false;
                                switch ($dynamic['type']) {
                                    // Si c'est une définition par recherche dans la DB
                                    case 'sql': {
                                        // Si la requête existe
                                        if (isset($dynamic['request']) && is_string($dynamic['request'])) {
                                            // On récupère la requête dans la DB
                                            $values = $db->select($dynamic['request']);
                                            // Si on a trouvé des valeurs
                                            if (count($values)) {
                                                // On prend la première
                                                $values = $values[0];
                                                // Si on a définit un champ, on l'utilise sinon on prend par défaut
                                                if (isset($dynamic['field']) && is_string($dynamic['field'])) {
                                                    $path = $values->{$dynamic['field']};
                                                } else {
                                                    $path = $values->path;
                                                }

                                                // S'il y a une base
                                                if (isset($dynamic['base']) && is_string($dynamic['base'])) {
                                                    $base = $dynamic['base'];
                                                    // Si la base finit par / on le retire
                                                    $base = rtrim($base, '/');
                                                    // Si le chemin commence par / on le retire
                                                    $path = ltrim($path, '/');
                                                    // On joint les deux avec un /
                                                    $path = $base . '/' . $path;
                                                }

                                                // On ajoute le namespace
                                                $view->addNamespace($namespace, base_path($path));
                                                $added = true;
                                            }
                                        }
                                        break;
                                    }
                                    // Si c'est une définition par closure
                                    case 'function': {
                                        // Si la closure existe
                                        if (isset($dynamic['function']) && is_callable($dynamic['function'])) {
                                            // On lance la closure
                                            $path = $dynamic['function']();

                                            // Si ca retourne un chemin
                                            if ($path && is_string($path)) {
                                                // S'il y a une base
                                                if (isset($dynamic['base']) && is_string($dynamic['base'])) {
                                                    $base = $dynamic['base'];
                                                    // Si la base finit par / on le retire
                                                    $base = rtrim($base, '/');
                                                    // Si le chemin commence par / on le retire
                                                    $path = ltrim($path, '/');
                                                    // On joint les deux avec un /
                                                    $path = $base . '/' . $path;
                                                }

                                                // On ajoute le namespace
                                                $view->addNamespace($namespace, base_path($path));
                                                $added = true;
                                            }
                                        }
                                        break;
                                    }
                                }
                                // Si on n'a pas ajouté le namespace ou qu'on veut l'ajouter
                                if (!$added || (isset($dynamic['prepend']) && $dynamic['prepend'])) {
                                    // On ajoute le namespace par défaut
                                    $view->addNamespace($namespace, base_path($options['default']));
                                }
                            } else {
                                // On ajoute le namespace
                                $view->addNamespace($namespace, base_path($options['default']));
                            }
                        } else {
                            // On ajoute le namespace
                            $view->addNamespace($namespace, base_path($options['default']));
                        }
                    }
                } elseif (is_string($options)) {
                    $view->addNamespace($namespace, base_path($options));
                }
            }
        }
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        //
    }
}
