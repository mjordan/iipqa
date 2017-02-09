The file mods-3-5-local.xsd is identical to the one at http://www.loc.gov/standards/mods/v3/mods-3-5.xsd
other than it points to local copies of xlink.xsd and xml.xsd, which are downloaded by Composer into this
directory (src/utils) as described in iipqa's main README.md file. Having the schema and all referenced schemas
cached locally not only speeds up validation immensely, it will prevent LoC from blacklisting your
IP address due to excessive HTTP requests.
