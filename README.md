# ABOUT

The aim of this bundle is to ease the generation of an asynchronous user interface for defining relations among two types of entities.

In this bundle, the 'host' entity is the one in which the interface is focused at a moment (of the entity you are editing). 'linked' entities are the entities connected to the first one through either ManyToMany or ManyToOne relations.

ATTENTION: this bundle is not stable and may change a lot.


# INSTALLATION

- clone from gitHub
- add composer.json

    "autoload": {
    ...
        "Jgzz\\MediaLinkerBundle": "vendor/jgzz/jgzzmedialinker/lib"
    }

- dump autoload
- add to AppKernel.php
- add to routing.yml

eg: 
JgzzMediaLinkerBundle:
  resource: "@JgzzMediaLinkerBundle/Resources/config/routing.yml"
  prefix:   /admin



# CONFIGURATION

You may configure the serveral 'Linkers' you may need in your '''config.yml'''. Add the mapping information under the internal name of each Linker.

eg: 'product_docs' is a Linker for relating some 'DocMedia' entity (desdendent of Sonata Media entities) with some 'Product' entity

Mapping: 

- builder: compulsory. May be 'sonata.builder' (for entities handled by a Sonata Admin), 'sonatamedia.builder' (for linking to a Sonata Media Entity). Alternatively, any service name implementing the '''LinkerBuilder''' interface.

- hostclass: host class for this linker (eg: Project\MyBundle\Entity\Product)

- linkedclass: linked class for this linker (eg: Project\MyBundle\Entity\DocMedia)

- candidateFetcher: fetcher to use for retrieving entities in the 'linkable' list. May be 'doctrine' (default), or 'sonatamedia'.

- fetcherOptions: optional setting. Further options passed to the '''Candidate Fetcher'''. For the '''sonatamedia''' candidate fetcher you may pass 'provider' and 'context' options to filter the results.

TODO: extend for custom fetchers

- row_template: template used for rendering the list of linked / candidate entities. You may extend the provided templates

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


# TODOS
- i18n
- remove 'name' attribute in templates for linked entities (not media entities)
