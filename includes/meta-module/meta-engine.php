<?php

namespace DetIt\Meta;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class MetaEngine {

    public function inject_meta_tags() {

        echo '<meta name="detit" content="active">';

    }

}