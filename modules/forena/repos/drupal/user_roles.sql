--ACCESS=access content
select name as role from {users_roles} ur JOIN {role} r ON r.rid = ur.rid
  WHERE ur.uid = :current_user
--INFO
type[current_user] = int