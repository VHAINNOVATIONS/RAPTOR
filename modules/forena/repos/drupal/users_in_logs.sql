--ACCESS=access administration pages
SELECT u.name, count(1) as total from {watchdog} w JOIN {users} u on u.uid=w.uid 
  GROUP BY u.name ORDER BY name asc