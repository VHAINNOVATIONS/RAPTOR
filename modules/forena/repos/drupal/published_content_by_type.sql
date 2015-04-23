--ACCESS=access content
SELECT nid, :content_type as selected_content_type from node WHERE type=:content_type
  and status=1 
  ORDER BY title
--INFO
; This demonstrates loading node entities.
entity_type = node
entity_id = nid
