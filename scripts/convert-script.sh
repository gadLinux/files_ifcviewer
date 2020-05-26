#!/bin/bash

export DOTNET_ROOT=/opt/dotnet/
#export PATH=$PATH:/usr/local/bin:/usr/bin:/snap/bin

IFC_CONVERTER=$(type --path IfcConvert)
METADATA_GENERATOR=$(type --path xeokit-metadata)
GLTF_GENERATOR=$(type --path COLLADA2GLTF-bin)
XKT_GENERATOR=$(type --path gltf2xkt)

if [ ! -x "$IFC_CONVERTER" ]; then
  echo "IFC Converter program not found"
  exit 1
fi

if [ ! -f "$METADATA_GENERATOR" ]; then
  echo "IFC Metadata generator program not found $METADATA_GENERATOR"
  exit 1
fi

if [ ! -x "$GLTF_GENERATOR" ]; then
  echo "IFC2GLTF program not found"
  exit 1
fi
if [ ! -x "$XKT_GENERATOR" ]; then
  echo "XKT packager program not found"
  exit 1
fi

IFC_PROJECT=$1
IFC_FILE=$2

echo "Converting file $IFC_FILE"
if [ -f "$IFC_FILE" ]; then 
  SHA_SUM=$(md5sum $IFC_FILE | cut -f1 -d' ')
  DAE_FILE=$(echo $IFC_FILE | sed -e "s/.ifc/-v$SHA_SUM.dae/g")
  GLTF_FILE=$(echo $IFC_FILE | sed -e "s/.ifc/-v$SHA_SUM.gltf/g")
  METADATA_FILE=$(echo $IFC_FILE | sed -e "s/.ifc/-v${SHA_SUM}_metadata.json/g")
  XKT_FILE=$(echo $IFC_FILE | sed -e "s/.ifc/-v$SHA_SUM.xkt/g")
  echo "Final file $XKT_FILE"
else
  echo "File $IFC_FILE not found"
  exit 1
fi
    
if [ ! -f "$XKT_FILE" ]; then
  if test -f "$IFC_FILE"; then
    echo "Generating files for $XKT_FILE"
    if [ ! -f "$METADATA_FILE" ]; then
      echo "Creating metadata file from $IFC_FILE -> $METADATA_FILE"
      $METADATA_GENERATOR "$IFC_FILE" "${METADATA_FILE}.tmp"
    else 
      echo "Metadata file $METADATA_FILE ready!"
    fi
    
    if [ -f "${METADATA_FILE}.tmp" ]; then
      echo "Adjusting project Id"
      cat "${METADATA_FILE}.tmp" | jq ".projectId = $1" > "${METADATA_FILE}"
      rm "${METADATA_FILE}.tmp"
    fi

    if [ ! -f "$DAE_FILE" ]; then
        echo "Converting IFC file $IFC_FILE to $DAE_FILE"
        $IFC_CONVERTER -j8 --use-element-guids $IFC_FILE $DAE_FILE --exclude=entities IfcOpeningElement
    else 
        echo "DAE file $DAE_FILE ready!"
    fi
    
    if [ ! -f "$GLTF_FILE" ]; then
        echo "Converting DAE file to $GLTF_FILE"
        $GLTF_GENERATOR -i $DAE_FILE -o $GLTF_FILE
    else 
        echo "GLTF file $GLTF_FILE ready!"
    fi
    
    if [ ! -f "$XKT_FILE" ]; then
        echo "Converting GLTF file to $XKT_FILE"
        $XKT_GENERATOR -s $GLTF_FILE -o $XKT_FILE
    else 
        echo "XKT file $XKT_FILE ready!"
    fi
  fi
else
	echo "Destination file exists"
fi

exit 0
