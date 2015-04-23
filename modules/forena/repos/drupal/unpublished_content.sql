--ACCESS=administer content
SELECT * FROM node 
  ORDER BY sticky DESC, created 
LIMIT :limit, 100
--INFO
type[limit] = int