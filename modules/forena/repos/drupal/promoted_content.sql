--ACCESS=access content
SELECT nid FROM node 
  WHERE promote=1
    AND status=1
  ORDER BY sticky DESC, created 
--IF=:limit
LIMIT :limit 
--ELSE 
LIMIT 10
--END
--INFO
type[limit]=int
entity_type = node
entity_id = nid
  
  