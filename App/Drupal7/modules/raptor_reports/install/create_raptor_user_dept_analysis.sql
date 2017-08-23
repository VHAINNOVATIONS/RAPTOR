
create procedure raptor_user_dept_analysis(in switch varchar(10))
begin
	drop table if exists temp_table1;
	
	create temporary table if not exists temp_table1 as
		(select t1.uid, t1.updated_dt, t1.username, t1.role_nm, t1.most_recent_login_dt, t2.IEN, t2.workflow_state, t2.suspended_dt, t2.approved_dt, t2.exam_completed_dt, t2.qa_completed_dt, t2.requested_dt
		from
			(select up.uid, up.updated_dt, up.username, up.role_nm, urat.most_recent_login_dt
			from
				`raptor_user_profile` up
			left join
				`raptor_user_recent_activity_tracking` urat
			on (up.uid = urat.uid)
			group by up.uid) t1
		left join
			(select wfh.initiating_uid, tt.IEN, tt.workflow_state, tt.suspended_dt, tt.approved_dt, tt.exam_completed_dt, tt.qa_completed_dt, tcol.requested_dt
			from
				`raptor_ticket_tracking` tt
			left join
				`raptor_ticket_collaboration` tcol
			on (tt.IEN = tcol.IEN)
			left join
				`raptor_ticket_workflow_history` wfh
			on (tt.IEN = wfh.IEN)
			group by tt.IEN) t2
		on (t1.uid = t2.initiating_uid));

	drop table if exists temp_table2;

	create temporary table if not exists temp_table2 as
	(select
		CASE
			WHEN approved_dt IS NOT NULL THEN YEAR(approved_dt)
			WHEN suspended_dt IS NOT NULL THEN YEAR(suspended_dt)
			WHEN exam_completed_dt IS NOT NULL THEN YEAR(exam_completed_dt)
			-- WHEN interpret_completed_dt IS NOT NULL THEN YEAR(interpret_completed_dt)
			WHEN qa_completed_dt IS NOT NULL THEN YEAR(qa_completed_dt)
			-- WHEN exam_details_committed_dt IS NOT NULL THEN YEAR(exam_details_committed_dt)
			else NULL
		END AS _year,
		CASE
			WHEN approved_dt IS NOT NULL THEN WEEK(approved_dt, 2)
			WHEN suspended_dt IS NOT NULL THEN WEEK(suspended_dt, 2)
			WHEN exam_completed_dt IS NOT NULL THEN WEEK(exam_completed_dt, 2)
			-- WHEN interpret_completed_dt IS NOT NULL THEN WEEK(interpret_completed_dt, 2)
			WHEN qa_completed_dt IS NOT NULL THEN WEEK(qa_completed_dt, 2)
			-- WHEN exam_details_committed_dt IS NOT NULL THEN WEEK(exam_details_committed_dt, 2)
			else NULL
		END AS week,		
		temp_table1.uid, temp_table1.IEN, temp_table1.username, temp_table1.role_nm, temp_table1.most_recent_login_dt,
		CASE
			WHEN approved_dt IS NOT NULL THEN 1
			ELSE 0
		end as Total_Approved, 
		CASE
			WHEN INSTR(workflow_state, 'CO')>0 THEN 1
			ELSE 0
		end as Count_Collab,
		CASE
			WHEN INSTR(workflow_state, 'PA')>0 THEN 1
			ELSE 0
		end as Total_Acknowledge,
		CASE
			WHEN INSTR(workflow_state, 'EC')>0 THEN 1
			WHEN INSTR(workflow_state, 'QA')>0 THEN 1
			ELSE 0
		end as Total_Complete,
		CASE
			WHEN INSTR(workflow_state, 'IA')>0 THEN 1
			ELSE 0
		end as Total_Suspend,
		NULL as Time_A_S,
		CASE
			WHEN qa_completed_dt IS NULL THEN TO_SECONDS(exam_completed_dt) - TO_SECONDS(approved_dt)
			WHEN exam_completed_dt IS NULL THEN TO_SECONDS(qa_completed_dt) - TO_SECONDS(approved_dt)
			WHEN qa_completed_dt IS NULL AND exam_completed_dt IS NULL THEN NULL
			ELSE least(TO_SECONDS(exam_completed_dt),TO_SECONDS(qa_completed_dt)) - TO_SECONDS(approved_dt)
		end as Time_A_C,
		NULL as Time_Collab,
		NULL as Total_Scheduled
	from temp_table1);

	drop table if exists temp_table3;

	case switch
		when 'user' then	
			create temporary table if not exists temp_table3 as
			(select _year, week, uid, username, role_nm, most_recent_login_dt, sum(Total_Approved),
			sum(Count_Collab), sum(Total_Acknowledge), sum(Total_Complete), sum(Total_Suspend),
			max(Time_A_S), avg(Time_A_S), max(Time_A_C), avg(Time_A_C),
			max(Time_Collab), avg(Time_Collab), sum(Total_Scheduled)
			from temp_table2
			group by uid, _year, week);
		when 'dept' then
			create temporary table if not exists temp_table3 as
			(select _year, week, role_nm, sum(Total_Approved),
			sum(Count_Collab), sum(Total_Acknowledge), sum(Total_Complete), sum(Total_Suspend),
			max(Time_A_S), avg(Time_A_S), max(Time_A_C), avg(Time_A_C),
			max(Time_Collab), avg(Time_Collab)
			from temp_table2
			group by _year, week);
		else
			select 'Oops! Switch case had a problem. Check passed value';
	end case;
end