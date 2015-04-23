--ACCESS=access administration pages
SELECT name, owner, weight FROM {system} WHERE status=1 AND 'module'=type ORDER BY name