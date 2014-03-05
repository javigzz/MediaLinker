# ABOUT

The aim of this bundle is to ease the generation of an asynchronous user interface for defining relations among two types of entities.

In this bundle, the 'host' entity is the one in which the interface is focused at a moment (of the entity you are editing). 'linked' entities are the entities connected to the first one through either ManyToMany or ManyToOne relations.



# CONFIGURATION

You may configure the serveral 'Linkers' you may need in your '''config.yml'''. Add the mapping information under the internal name of each Linker.

eg: 'docsdeprod' is a Linker for relating some 'Media' entity (desdendent of Sonata Media entities)

Mapping: 

- builder: compulsory. May be 'sonata.builder' (for entities handled by a Sonata Admin), 'sonatamedia.builder' (for linking to a Sonata Media Entity). Alternatively, any service name implementing the '''LinkerBuilder''' interface.

- hostclass: host class for this linker

- linkedclass: linked class for this linker

- candidateFetcher: fetcher to use for retrieving entities in the list 'linkable' list. May be 'doctrine' (default), or 'sonatamedia'

- fetcherOptions: optional setting. Further options passed to the '''Candidate Fetcher'''. For the '''sonatamedia''' CF you may pass 'provider' and 'context'

TODO: extend for custom fetchers
TODO: allow basic arbitrary filters for 'doctrine' fetcher

- row_template: template used for rendering the list of linked / candidate entities. You may extend the provided templates

jgzz_media_linker:
    mappings:
        docsdeprod:
            builder: sonatamedia.builder
            hostclass: Project\SiteBundle\Entity\Content
            linkedclass: Project\SiteBundle\Entity\Media
            candidateFetcher: sonatamedia
            fetcherOptions:
                context: default
                provider: sonata.media.provider.file
            row_template: JgzzMediaLinkerBundle:CRUD:linked_entity_row_base.html.twig.html.twig


# TODOS
- i18n mensajes interfaz
- bloqueo de interfaz mientras acción se ejecuta (nivel de linea)
- atributo 'name' en plantillas para linked
