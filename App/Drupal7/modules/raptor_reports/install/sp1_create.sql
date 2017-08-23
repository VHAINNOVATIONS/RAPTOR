CREATE PROCEDURE raptor_example_sp 
(OUT param1 INT)
BEGIN

  SELECT 
    COUNT(*) INTO param1 
  FROM raptor_user_profile;
  
END 


