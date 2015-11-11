CREATE PROCEDURE `raptor_user_dept_analysis`(IN switch VARCHAR(10))

BEGIN

	DROP TABLE IF EXISTS temp1;

	DROP TABLE IF EXISTS temp2;

	DROP TABLE IF EXISTS temp3;

	DROP TABLE IF EXISTS temp4;



	CREATE TEMPORARY TABLE IF NOT EXISTS temp1 AS

	(SELECT t.ien, t.initiating_uid, SUM(t.Count_Collab) AS Count_Collab, SUM(t.Total_Acknowledge) AS Total_Acknowledge,

		SUM(t.Total_Complete) AS Total_Complete, SUM(t.Total_Suspend) AS Total_Suspend

	FROM

		(SELECT wfh.ien, wfh.initiating_uid,

			IF(wfh.old_workflow_state <=> 'CO', 1, NULL) AS Count_Collab,

			IF(wfh.old_workflow_state <=> 'PA', 1, NULL) AS Total_Acknowledge,

			IF(wfh.old_workflow_state <=> 'EC' OR wfh.old_workflow_state <=> 'QA', 1, NULL) AS Total_Complete,

			IF(wfh.old_workflow_state <=> 'IA', 1, NULL) AS Total_Suspend

		FROM `raptor_ticket_workflow_history` wfh) t

	GROUP BY t.ien);



	CREATE TEMPORARY TABLE IF NOT EXISTS temp2 AS

	(SELECT t.ien,

		CASE

			WHEN t.approved_dt IS NOT NULL THEN YEAR(t.approved_dt)

			WHEN t.suspended_dt IS NOT NULL THEN YEAR(t.suspended_dt)

			WHEN t.exam_completed_dt IS NOT NULL THEN YEAR(t.exam_completed_dt)

			WHEN t.interpret_completed_dt IS NOT NULL THEN YEAR(t.interpret_completed_dt)

			WHEN t.qa_completed_dt IS NOT NULL THEN YEAR(t.qa_completed_dt)

			WHEN t.exam_details_committed_dt IS NOT NULL THEN YEAR(t.exam_details_committed_dt)

			ELSE NULL

		END AS _year,

		CASE

			WHEN t.approved_dt IS NOT NULL THEN QUARTER(t.approved_dt)

			WHEN t.suspended_dt IS NOT NULL THEN QUARTER(t.suspended_dt)

			WHEN t.exam_completed_dt IS NOT NULL THEN QUARTER(t.exam_completed_dt)

			WHEN t.interpret_completed_dt IS NOT NULL THEN QUARTER(t.interpret_completed_dt)

			WHEN t.qa_completed_dt IS NOT NULL THEN QUARTER(t.qa_completed_dt)

			WHEN t.exam_details_committed_dt IS NOT NULL THEN QUARTER(t.exam_details_committed_dt)

			ELSE NULL

		END AS quarter,

		CASE

			WHEN t.approved_dt IS NOT NULL THEN WEEK(t.approved_dt, 2)

			WHEN t.suspended_dt IS NOT NULL THEN WEEK(t.suspended_dt, 2)

			WHEN t.exam_completed_dt IS NOT NULL THEN WEEK(t.exam_completed_dt, 2)

			WHEN t.interpret_completed_dt IS NOT NULL THEN WEEK(t.interpret_completed_dt, 2)

			WHEN t.qa_completed_dt IS NOT NULL THEN WEEK(t.qa_completed_dt, 2) 

			WHEN t.exam_details_committed_dt IS NOT NULL THEN WEEK(t.exam_details_committed_dt, 2)

			ELSE NULL

		END AS week,

			CASE

			WHEN t.approved_dt IS NOT NULL THEN DAYOFYEAR(t.approved_dt)

			WHEN t.suspended_dt IS NOT NULL THEN DAYOFYEAR(t.suspended_dt)

			WHEN t.exam_completed_dt IS NOT NULL THEN DAYOFYEAR(t.exam_completed_dt)

			WHEN t.interpret_completed_dt IS NOT NULL THEN DAYOFYEAR(t.interpret_completed_dt)

			WHEN t.qa_completed_dt IS NOT NULL THEN DAYOFYEAR(t.qa_completed_dt)

			WHEN t.exam_details_committed_dt IS NOT NULL THEN DAYOFYEAR(t.exam_details_committed_dt)

			ELSE NULL

		END AS day,

		IF(t.approved_dt IS NOT NULL, 1, NULL) AS Total_Approved,

		IF(t.workflow_state <=> 'CO', 1, NULL) AS Count_Collab,

		IF(t.workflow_state <=> 'PA', 1, NULL) AS Total_Acknowledge,

		IF(t.workflow_state <=> 'EC' OR t.workflow_state <=> 'QA', 1, NULL) AS Total_Complete,

		IF(t.workflow_state <=> 'IA', 1, NULL) AS Total_Suspend,

		IF(t.approved_dt IS NOT NULL AND t.scheduled_dt IS NOT NULL, (TO_SECONDS(t.scheduled_dt) - TO_SECONDS(t.approved_dt)), NULL) AS Time_A_S,

		IF(t.scheduled_dt IS NOT NULL, 1, NULL) AS Total_Scheduled,

		NULL AS Time_Collab,

		CASE

			WHEN t.qa_completed_dt IS NULL THEN (TO_SECONDS(t.exam_completed_dt) - TO_SECONDS(t.approved_dt))

			WHEN t.exam_completed_dt IS NULL THEN (TO_SECONDS(t.qa_completed_dt) - TO_SECONDS(t.approved_dt))

			WHEN t.qa_completed_dt IS NULL AND t.exam_completed_dt IS NULL THEN NULL

			ELSE least (TO_SECONDS(t.exam_completed_dt),TO_SECONDS(t.qa_completed_dt)) - TO_SECONDS(t.approved_dt)

		END AS Time_A_C,
		t.modality_abbr

	FROM

		(SELECT tt.IEN, tt.workflow_state, tt.suspended_dt, tt.approved_dt, tt.exam_completed_dt, tt.interpret_completed_dt,

			tt.qa_completed_dt, tt.exam_details_committed_dt, tcol.requested_dt, GROUP_CONCAT(pl.modality_abbr SEPARATOR ', ') AS modality_abbr,

			st.scheduled_dt

		FROM 

			`raptor_ticket_tracking` tt

		LEFT JOIN

			`raptor_ticket_collaboration` tcol

		ON (tt.IEN = tcol.IEN)

		LEFT JOIN

			`raptor_ticket_protocol_settings` tps

		ON (tt.IEN = tps.IEN)

		LEFT JOIN

			`raptor_protocol_lib` pl

		ON (tps.`primary_protocol_shortname` = pl.`protocol_shortname`)

		LEFT JOIN

			`raptor_schedule_track` st

		ON (tt.IEN = st.IEN)

		GROUP BY tt.IEN) t

	);



	CREATE TEMPORARY TABLE IF NOT EXISTS temp3 AS

	(SELECT

		t1.uid, t1.username, t1.role_nm, t1.most_recent_login_dt, SUM(t2.Collab_Req) AS Collab_Req, SUM(t3.Collab_Pick) AS Collab_Pick

	FROM

		(SELECT up.uid, up.username, up.role_nm, urat.most_recent_login_dt

		FROM

			`raptor_user_profile` up

		LEFT JOIN

			`raptor_user_recent_activity_tracking` urat

		ON (up.uid = urat.uid)

		GROUP BY up.uid) t1

	LEFT JOIN

		(SELECT `requester_uid`, 1 AS Collab_Req FROM `raptor_ticket_collaboration`) t2

	ON (t1.uid = t2.`requester_uid`)

	LEFT JOIN

		(SELECT `collaborator_uid`, 1 AS Collab_Pick FROM `raptor_ticket_collaboration`) t3

	ON (t1.uid = t3.`collaborator_uid`)

	GROUP BY t1.uid);



	CASE switch

		WHEN 'user' THEN

			CREATE TEMPORARY TABLE IF NOT EXISTS temp4 AS

			(SELECT

				temp3.uid, temp3.username, temp3.role_nm, temp3.most_recent_login_dt, temp3.Collab_Req, temp3.Collab_Pick,

				temp2.modality_abbr, temp2._year, temp2.quarter, temp2.week, temp2.day,

				AVG(ABS(temp2.Time_A_C)) AS Avg_Time_A_C, MAX(ABS(temp2.Time_A_C)) AS Max_Time_A_C,

				AVG(ABS(temp2.Time_A_S)) AS Avg_Time_A_S, MAX(ABS(temp2.Time_A_S)) AS Max_Time_A_S,

				AVG(ABS(temp2.Time_Collab)) AS Avg_Time_Collab, MAX(ABS(temp2.Time_Collab)) AS Max_Time_Collab,

				SUM(temp2.Total_Approved) AS Total_Approved, SUM(temp2.Total_Scheduled) AS Total_Scheduled,

				(SUM(temp1.Count_Collab) + SUM(temp2.Count_Collab)) AS Count_Collab,

				(SUM(temp1.Total_Acknowledge) + SUM(temp2.Total_Acknowledge)) AS Total_Acknowledge,

				(SUM(temp1.Total_Complete) + SUM(temp2.Total_Complete)) AS Total_Complete,

				(SUM(temp1.Total_Suspend) + SUM(temp2.Total_Suspend)) AS Total_Suspend

			FROM

				`temp3`

			LEFT JOIN

				`temp1`

			ON (temp3.uid = temp1.initiating_uid)

			LEFT JOIN

				`temp2`

			ON (temp1.ien = temp2.ien)

			GROUP BY uid, _year, quarter, week, day);

			

		WHEN 'dept' THEN

			CREATE TEMPORARY TABLE IF NOT EXISTS temp4 AS

			(SELECT

				temp3.Collab_Req, temp3.Collab_Pick,

				temp2.modality_abbr, temp2._year, temp2.quarter, temp2.week, temp2.day,

				AVG(ABS(temp2.Time_A_C)) AS Avg_Time_A_C, MAX(ABS(temp2.Time_A_C)) AS Max_Time_A_C,

				AVG(ABS(temp2.Time_A_S)) AS Avg_Time_A_S, MAX(ABS(temp2.Time_A_S)) AS Max_Time_A_S,

				AVG(ABS(temp2.Time_Collab)) AS Avg_Time_Collab, MAX(ABS(temp2.Time_Collab)) AS Max_Time_Collab,

				SUM(temp2.Total_Approved) AS Total_Approved,

				(SUM(temp1.Count_Collab) + SUM(temp2.Count_Collab)) AS Count_Collab,

				(SUM(temp1.Total_Acknowledge) + SUM(temp2.Total_Acknowledge)) AS Total_Acknowledge,

				(SUM(temp1.Total_Complete) + SUM(temp2.Total_Complete)) AS Total_Complete,

				(SUM(temp1.Total_Suspend) + SUM(temp2.Total_Suspend)) AS Total_Suspend

			FROM

				`temp3`

			LEFT JOIN

				`temp1`

			ON (temp3.uid = temp1.initiating_uid)

			LEFT JOIN

				`temp2`

			ON (temp1.ien = temp2.ien)

			GROUP BY _year, quarter, week, day);

		ELSE

			SELECT 'Oops! Switch case had a problem. Check passed value';

	END CASE;

END