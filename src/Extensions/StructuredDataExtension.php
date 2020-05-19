<?php

namespace MichelSteege\StructuredData\Extensions;

use SilverStripe\Core\Extension;

class StructuredDataExtension extends Extension {

    public function StructuredData(){
        return \MichelSteege\StructuredData\StructuredData::render();
    }

}
