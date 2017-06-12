# DataStore

DataStore is an API desgined to store data with meta information to be searched later when a specific set of data is needed.

## Reading Data
The index.php endpoint retrieves the data based on the conditions given in the JSON Post request.

	{
		'conditions': [
			"has myKey",
			"comp platform =myPlatform"
		]
	}

The conditions key must be an array with valid conditions. Condition syntax is "action key value" split by a space.

### has condition

Has only takes a key, and checks if that data file has a key value pair with that key.

### comp condition

Comp takes a key and value pair and checks the value according to the operation given. The operation should be the first character in the value as shown in the JSON above, the operations <, >, and = are supported, but only = will work for non-numeric values.

## Return JSON

The call above returns the following JSON

	{
		"result": "success",
		"imageIds": [
			"id1",
			"id2",
			...
		]
	}

To download each individual file, use the Url "/file/$id".