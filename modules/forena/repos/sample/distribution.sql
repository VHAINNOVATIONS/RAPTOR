select * from user_distribution WHERE 
  state in (:state)
  order by city, state
