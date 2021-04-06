<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInitee3011d7393ffca67dec83e48b82b684
{
    public static $files = array (
        'a4a119a56e50fbb293281d9a48007e0e' => __DIR__ . '/..' . '/symfony/polyfill-php80/bootstrap.php',
        '71ecd0286a4e74fd8732297fb587023c' => __DIR__ . '/..' . '/thingengineer/mysqli-database-class/MysqliDb.php',
        'd383f1ec7b1e54a09cb53eb6fcf751e0' => __DIR__ . '/..' . '/thingengineer/mysqli-database-class/dbObject.php',
        '20540fee703fb26b5e46ea2c1cb78d78' => __DIR__ . '/..' . '/semsol/arc2/ARC2.php',
        'e98ae83aaf42bb653417458ae576b2d4' => __DIR__ . '/..' . '/semsol/arc2/ARC2_Class.php',
        'c1bf40f92dcf21e0b8a469c0c90a013a' => __DIR__ . '/..' . '/semsol/arc2/ARC2_getFormat.php',
        '3310f2d48ccb6a1d63ace2ff9974c51f' => __DIR__ . '/..' . '/semsol/arc2/ARC2_getPreferredFormat.php',
        'ee274324607689550f35d8b41e0249a5' => __DIR__ . '/..' . '/semsol/arc2/ARC2_Graph.php',
        '0a98b16bf36d5a42930e1567faf36d4a' => __DIR__ . '/..' . '/semsol/arc2/ARC2_Reader.php',
        '9a394445f90dac3d0543f751921bfbce' => __DIR__ . '/..' . '/semsol/arc2/ARC2_Resource.php',
    );

    public static $prefixLengthsPsr4 = array (
        'S' => 
        array (
            'Symfony\\Polyfill\\Php80\\' => 23,
            'Symfony\\Contracts\\Service\\' => 26,
            'Symfony\\Contracts\\Cache\\' => 24,
            'Symfony\\Component\\VarExporter\\' => 30,
            'Symfony\\Component\\Cache\\' => 24,
        ),
        'P' => 
        array (
            'Psr\\SimpleCache\\' => 16,
            'Psr\\Log\\' => 8,
            'Psr\\Container\\' => 14,
            'Psr\\Cache\\' => 10,
        ),
        'A' => 
        array (
            'ARC2\\' => 5,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Symfony\\Polyfill\\Php80\\' => 
        array (
            0 => __DIR__ . '/..' . '/symfony/polyfill-php80',
        ),
        'Symfony\\Contracts\\Service\\' => 
        array (
            0 => __DIR__ . '/..' . '/symfony/service-contracts',
        ),
        'Symfony\\Contracts\\Cache\\' => 
        array (
            0 => __DIR__ . '/..' . '/symfony/cache-contracts',
        ),
        'Symfony\\Component\\VarExporter\\' => 
        array (
            0 => __DIR__ . '/..' . '/symfony/var-exporter',
        ),
        'Symfony\\Component\\Cache\\' => 
        array (
            0 => __DIR__ . '/..' . '/symfony/cache',
        ),
        'Psr\\SimpleCache\\' => 
        array (
            0 => __DIR__ . '/..' . '/psr/simple-cache/src',
        ),
        'Psr\\Log\\' => 
        array (
            0 => __DIR__ . '/..' . '/psr/log/Psr/Log',
        ),
        'Psr\\Container\\' => 
        array (
            0 => __DIR__ . '/..' . '/psr/container/src',
        ),
        'Psr\\Cache\\' => 
        array (
            0 => __DIR__ . '/..' . '/psr/cache/src',
        ),
        'ARC2\\' => 
        array (
            0 => __DIR__ . '/..' . '/semsol/arc2/src/ARC2',
        ),
    );

    public static $classMap = array (
        'ARC2_AtomParser' => __DIR__ . '/..' . '/semsol/arc2/parsers/ARC2_AtomParser.php',
        'ARC2_CBJSONParser' => __DIR__ . '/..' . '/semsol/arc2/parsers/ARC2_CBJSONParser.php',
        'ARC2_JSONLDSerializer' => __DIR__ . '/..' . '/semsol/arc2/serializers/ARC2_JSONLDSerializer.php',
        'ARC2_JSONParser' => __DIR__ . '/..' . '/semsol/arc2/parsers/ARC2_JSONParser.php',
        'ARC2_LegacyHTMLSerializer' => __DIR__ . '/..' . '/semsol/arc2/serializers/ARC2_LegacyHTMLSerializer.php',
        'ARC2_LegacyJSONSerializer' => __DIR__ . '/..' . '/semsol/arc2/serializers/ARC2_LegacyJSONSerializer.php',
        'ARC2_LegacyXMLParser' => __DIR__ . '/..' . '/semsol/arc2/parsers/ARC2_LegacyXMLParser.php',
        'ARC2_LegacyXMLSerializer' => __DIR__ . '/..' . '/semsol/arc2/serializers/ARC2_LegacyXMLSerializer.php',
        'ARC2_MemStore' => __DIR__ . '/..' . '/semsol/arc2/store/ARC2_MemStore.php',
        'ARC2_MicroRDFSerializer' => __DIR__ . '/..' . '/semsol/arc2/serializers/ARC2_MicroRDFSerializer.php',
        'ARC2_NTriplesSerializer' => __DIR__ . '/..' . '/semsol/arc2/serializers/ARC2_NTriplesSerializer.php',
        'ARC2_POSHRDFSerializer' => __DIR__ . '/..' . '/semsol/arc2/serializers/ARC2_POSHRDFSerializer.php',
        'ARC2_RDFJSONSerializer' => __DIR__ . '/..' . '/semsol/arc2/serializers/ARC2_RDFJSONSerializer.php',
        'ARC2_RDFParser' => __DIR__ . '/..' . '/semsol/arc2/parsers/ARC2_RDFParser.php',
        'ARC2_RDFSerializer' => __DIR__ . '/..' . '/semsol/arc2/serializers/ARC2_RDFSerializer.php',
        'ARC2_RDFXMLParser' => __DIR__ . '/..' . '/semsol/arc2/parsers/ARC2_RDFXMLParser.php',
        'ARC2_RDFXMLSerializer' => __DIR__ . '/..' . '/semsol/arc2/serializers/ARC2_RDFXMLSerializer.php',
        'ARC2_RSS10Serializer' => __DIR__ . '/..' . '/semsol/arc2/serializers/ARC2_RSS10Serializer.php',
        'ARC2_RSSParser' => __DIR__ . '/..' . '/semsol/arc2/parsers/ARC2_RSSParser.php',
        'ARC2_RemoteStore' => __DIR__ . '/..' . '/semsol/arc2/store/ARC2_RemoteStore.php',
        'ARC2_SGAJSONParser' => __DIR__ . '/..' . '/semsol/arc2/parsers/ARC2_SGAJSONParser.php',
        'ARC2_SPARQLParser' => __DIR__ . '/..' . '/semsol/arc2/parsers/ARC2_SPARQLParser.php',
        'ARC2_SPARQLPlusParser' => __DIR__ . '/..' . '/semsol/arc2/parsers/ARC2_SPARQLPlusParser.php',
        'ARC2_SPARQLXMLResultParser' => __DIR__ . '/..' . '/semsol/arc2/parsers/ARC2_SPARQLXMLResultParser.php',
        'ARC2_SPOGParser' => __DIR__ . '/..' . '/semsol/arc2/parsers/ARC2_SPOGParser.php',
        'ARC2_SemHTMLParser' => __DIR__ . '/..' . '/semsol/arc2/parsers/ARC2_SemHTMLParser.php',
        'ARC2_Store' => __DIR__ . '/..' . '/semsol/arc2/store/ARC2_Store.php',
        'ARC2_StoreAskQueryHandler' => __DIR__ . '/..' . '/semsol/arc2/store/ARC2_StoreAskQueryHandler.php',
        'ARC2_StoreAtomLoader' => __DIR__ . '/..' . '/semsol/arc2/store/ARC2_StoreAtomLoader.php',
        'ARC2_StoreCBJSONLoader' => __DIR__ . '/..' . '/semsol/arc2/store/ARC2_StoreCBJSONLoader.php',
        'ARC2_StoreConstructQueryHandler' => __DIR__ . '/..' . '/semsol/arc2/store/ARC2_StoreConstructQueryHandler.php',
        'ARC2_StoreDeleteQueryHandler' => __DIR__ . '/..' . '/semsol/arc2/store/ARC2_StoreDeleteQueryHandler.php',
        'ARC2_StoreDescribeQueryHandler' => __DIR__ . '/..' . '/semsol/arc2/store/ARC2_StoreDescribeQueryHandler.php',
        'ARC2_StoreDumpQueryHandler' => __DIR__ . '/..' . '/semsol/arc2/store/ARC2_StoreDumpQueryHandler.php',
        'ARC2_StoreDumper' => __DIR__ . '/..' . '/semsol/arc2/store/ARC2_StoreDumper.php',
        'ARC2_StoreEndpoint' => __DIR__ . '/..' . '/semsol/arc2/store/ARC2_StoreEndpoint.php',
        'ARC2_StoreHelper' => __DIR__ . '/..' . '/semsol/arc2/store/ARC2_StoreHelper.php',
        'ARC2_StoreInsertQueryHandler' => __DIR__ . '/..' . '/semsol/arc2/store/ARC2_StoreInsertQueryHandler.php',
        'ARC2_StoreLoadQueryHandler' => __DIR__ . '/..' . '/semsol/arc2/store/ARC2_StoreLoadQueryHandler.php',
        'ARC2_StoreQueryHandler' => __DIR__ . '/..' . '/semsol/arc2/store/ARC2_StoreQueryHandler.php',
        'ARC2_StoreRDFXMLLoader' => __DIR__ . '/..' . '/semsol/arc2/store/ARC2_StoreRDFXMLLoader.php',
        'ARC2_StoreRSSLoader' => __DIR__ . '/..' . '/semsol/arc2/store/ARC2_StoreRSSLoader.php',
        'ARC2_StoreSGAJSONLoader' => __DIR__ . '/..' . '/semsol/arc2/store/ARC2_StoreSGAJSONLoader.php',
        'ARC2_StoreSPOGLoader' => __DIR__ . '/..' . '/semsol/arc2/store/ARC2_StoreSPOGLoader.php',
        'ARC2_StoreSelectQueryHandler' => __DIR__ . '/..' . '/semsol/arc2/store/ARC2_StoreSelectQueryHandler.php',
        'ARC2_StoreSemHTMLLoader' => __DIR__ . '/..' . '/semsol/arc2/store/ARC2_StoreSemHTMLLoader.php',
        'ARC2_StoreTableManager' => __DIR__ . '/..' . '/semsol/arc2/store/ARC2_StoreTableManager.php',
        'ARC2_StoreTurtleLoader' => __DIR__ . '/..' . '/semsol/arc2/store/ARC2_StoreTurtleLoader.php',
        'ARC2_TurtleParser' => __DIR__ . '/..' . '/semsol/arc2/parsers/ARC2_TurtleParser.php',
        'ARC2_TurtleSerializer' => __DIR__ . '/..' . '/semsol/arc2/serializers/ARC2_TurtleSerializer.php',
        'Attribute' => __DIR__ . '/..' . '/symfony/polyfill-php80/Resources/stubs/Attribute.php',
        'Stringable' => __DIR__ . '/..' . '/symfony/polyfill-php80/Resources/stubs/Stringable.php',
        'UnhandledMatchError' => __DIR__ . '/..' . '/symfony/polyfill-php80/Resources/stubs/UnhandledMatchError.php',
        'ValueError' => __DIR__ . '/..' . '/symfony/polyfill-php80/Resources/stubs/ValueError.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInitee3011d7393ffca67dec83e48b82b684::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInitee3011d7393ffca67dec83e48b82b684::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInitee3011d7393ffca67dec83e48b82b684::$classMap;

        }, null, ClassLoader::class);
    }
}
