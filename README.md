# natepage/dynamo-db-repository

A simple repository abstraction for AWS DynamoDB

## TODO

- [X] Implement abstraction based on learnings from previous projects
- [X] Auto mapping of objects to DynamoDB items (PropertyTypeInfo, DoctrineMetadata, etc.)
    - Allow implementations to customize the outcome of the mapping (e.g. to add additional fields, or to exclude certain fields)
    - Look at https://github.com/jolicode/automapper
- [X] Implement package specific exceptions
- [X] Implement a registry to get repository instances by class name
- [X] Implement Symfony bundle
- [ ] Implement Doctrine bridge to seamlessly use the repository with Doctrine entities
