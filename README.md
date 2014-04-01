# ABOUT

The aim of this bundle is to ease the generation of an asynchronous user interface for defining relations among two types of entities.

In this bundle, the `host` entity is the one in which the interface is focused at a moment (of the entity you are editing). `linked` entities are the entities connected to the first one through either ManyToMany or ManyToOne relations.

ATTENTION: this bundle is not stable and may change a lot.

DO NOT USE THIS OUT OF A FIREWALL SINCE NO ACCESS CONTROL LAYER IS IMPLEMENTED

# Installation

- clone from gitHub

- add composer.json

    "autoload": {
    ...
        "Jgzz\\MediaLinkerBundle": "vendor/jgzz/jgzzmedialinker/lib"
    }

(no packagist so far)


## Add to AppKernel

Add bundle to your app's AppKernel.php

```coffee
new Jgzz\MediaLinkerBundle\JgzzMediaLinkerBundle(),
```


## Add to routing

Add the routing of the bundle to our app `routing.yml`. For example:

```coffee
JgzzMediaLinkerBundle:
  resource: "@JgzzMediaLinkerBundle/Resources/config/routing.yml"
  prefix:   /admin
```

## Add public resources

You need to add public front end resources (js and css) provided by de bundle.


```coffee
<link rel="stylesheet" href="{{ asset('bundles/jgzzmedialinker/css/main.css') }}" type="text/css" />
```
...

```coffee
<script src="{{ asset('bundles/jgzzmedialinker/js/jgzzlinker.js') }}" type="text/javascript"></script>
```

Also, if not added previously, add the the jquery plugin `jQuery Form Plugin`. It is bundled here for convenience.
```coffee
<script src="{{ asset('bundles/jgzzmedialinker/js/vendor/jquery.form.2.64.js') }}" type="text/javascript"></script>
```

Don't forget to dump the assets first: `php app/console assets:install web`

## Dump autoload

Regenerate the namespace map with composer.

eg:

```coffee
composer dump-autoload
```


# Configuration

You may configure the serveral 'Linkers' you may need in your '''config.yml'''. Add the mapping information under the internal name of each Linker.

eg: 'product_docs' is a Linker for relating some 'DocMedia' entity (desdendent of Sonata Media entities) with some 'Product' entity

```coffee
jgzz_media_linker:
    mappings:
        product_docs:
            builder: sonatamedia.builder
            hostclass: Project\SiteBundle\Entity\Content
            linkedclass: Project\SiteBundle\Entity\Media
            candidateFetcher: sonatamedia
            fetcherOptions:
                context: default
                provider: sonata.media.provider.file
            row_template: JgzzMediaLinkerBundle:CRUD:linked_entity_row_base.html.twig.html.twig
```

Mapping: 

- `builder`: compulsory. May be 'sonata.builder' (for entities handled by a Sonata Admin), 'sonatamedia.builder' (for linking to a Sonata Media Entity). Alternatively, any service name implementing the '''LinkerBuilder''' interface.

- `hostclass`: host class for this linker (eg: Project\MyBundle\Entity\Product)

- `linkedclass`: linked class for this linker (eg: Project\MyBundle\Entity\DocMedia)

- `candidateFetcher`: fetcher to use for retrieving entities in the 'linkable' list. May be 'doctrine' (default), or 'sonatamedia'.

- `fetcherOptions`: optional setting. Further options passed to the '''Candidate Fetcher'''. For the '''sonatamedia''' candidate fetcher you may pass 'provider' and 'context' options to filter the results.


- `row_template`: template used for rendering the list of linked / candidate entities. You may extend the provided templates

TODO: extend for custom candidate fetchers


# Twig Helpers

This bundle includes three helpers to generate three basic admin panels for managing related entities. These helpers get two arguments:

- `name of the linker`, as stated in the config.yml under `mappings`
- `id of the host entity`

For the JS magic to take place on these panels you need to enclose them within a `class="jzlink-panel` element.

## Current linked entities

Renders a panel with all the related entities and action buttons to edit, unlink and delete the related entities. 

eg:

```coffee
<div class="jzlink-panel">
{{ jzlinker_render_panel('linker_name', hostentity.id)|raw }}
</div>
```

## New related entity

Renders the form for a new related entity.

eg: 

```coffee
<div class="jzlink-panel">
{{ jzlinker_render_panel_form('linker_name', hostentity.id)|raw }}
</div>
```

## Candidates to link

Renders panel with a list of candidate entities to be linked to the 'host' entity, as stated by the configured `candidateFetcher`

```coffee
<div class="jzlink-panel">
{{ jzlinker_render_panel_candidates('linker_name', hostentity.id)|raw }}
</div>
```

NOTE: for them to be releted one to another the panles must be enclosed by the same `<div class="jzlink-panel">`.



# TODOS
- i18n
- security layer
- remove 'name' attribute in templates for linked entities (not media entities)
