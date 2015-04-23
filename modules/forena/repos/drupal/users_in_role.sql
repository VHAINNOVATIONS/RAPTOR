--ACCESS=administer users
SELECT u.uid,u.name FROM {role} r JOIN {users_roles} ur ON r.rid=ur.rid
  JOIN {users} u ON ur.uid=u.uid
  WHERE r.rid = :rid
--INFO
type[role]=int