using System;
using System.Configuration;
using System.Collections.Generic;
using System.Linq;
using System.Web;
using System.Data.Sql;
using System.Data.SqlClient;
using System.Net;
using System.Data;
using gov.va.medora.mdws.dto;

namespace gov.va.medora.mdws.dao.sql
{
    public class UsageDao
    {
        string _connectionString;

        public string ConnectionString
        {
            get { return _connectionString; }
            set { _connectionString = value; }
        }

        public UsageDao()
        {
            try
            {
                MySession session = HttpContext.Current.Session["MySession"] as MySession;
                _connectionString = session.MdwsConfiguration.SqlConnectionString;
            }
            catch (Exception)
            {
                throw;
            }
        }

        public UsageDao(string connectionString)
        {
            _connectionString = connectionString;
        }

        /// <summary>
        /// Delete a session by it's ID and all the corresponding saved requests
        /// </summary>
        /// <param name="sessionId">The session ID to be deleted</param>
        /// <returns>
        /// BoolTO.trueOrFalse = true if successful. 
        /// BoolTO.trueOrFalse = false if session ID not found.
        /// BoolTO.fault will be != null on error
        /// </returns>
        public BoolTO deleteSession(string sessionId)
        {
            if (String.IsNullOrEmpty(sessionId) || sessionId.Length != 24) // valid session IDs are 24 characters
            {
                throw new ArgumentException("Need a valid session ID!");
            }
            BoolTO result = new BoolTO();
            SqlConnection conn = new SqlConnection();
            SqlTransaction tx = null;

            try
            {
                conn = getConnection();
                tx = conn.BeginTransaction();
                SqlDataAdapter adapter = buildDeleteSessionAdapter(sessionId, conn, tx);
                int i = adapter.DeleteCommand.ExecuteNonQuery();
                tx.Commit();
                if (i == 0)
                {
                    result.trueOrFalse = false; // query succeeded but no rows were affected
                }
                else
                {
                    result.trueOrFalse = true;
                }
                return result;
            }
            catch (Exception exc)
            {
                if (tx != null && tx.Connection != null) // transaction connection should be null if completed
                {
                    tx.Rollback();
                }
                result.fault = new FaultTO(exc);
                return result;
            }
            finally
            {
                conn.Close();
                if (tx != null)
                {
                    tx.Dispose();
                }
            }
        }

        /// <summary>
        /// Save a ApplicationSession object and it's requests
        /// </summary>
        /// <param name="session">The session object to save</param>
        /// <returns>
        /// BoolTO.trueOrFalse = true on success
        /// BoolTO.fault will be != null on error
        /// </returns>
        public BoolTO saveSession(ApplicationSession session)
        {
            BoolTO result = new BoolTO();
            SqlConnection conn = new SqlConnection();
            SqlTransaction tx = null;

            try
            {
                conn = getConnection();
                tx = conn.BeginTransaction();
                SqlDataAdapter adapter = buildInsertSessionAdapter(session, conn, tx);
                adapter.InsertCommand.ExecuteNonQuery();
                foreach (ApplicationRequest request in session.Requests)
                {
                    adapter = buildInsertRequestAdapter(request, conn, tx);
                    adapter.InsertCommand.ExecuteNonQuery();
                }
                tx.Commit();
                result.trueOrFalse = true;
                return result;
            }
            catch (Exception exc)
            {
                if (tx != null && tx.Connection != null) // transaction connection should be null if completed
                {
                    tx.Rollback();
                }
                result.fault = new FaultTO(exc);
                return result;
            }
            finally
            {
                conn.Close();
                if (tx != null)
                {
                    tx.Dispose();
                }
            }
        }

        /// <summary>
        /// Get all the MDWS sessions and the related requests for a given date range
        /// </summary>
        /// <param name="start">The start date</param>
        /// <param name="end">The end date</param>
        /// <returns>An ApplicationSessionsTO object - will have a FaultTO if an error occurs</returns>
        public ApplicationSessionsTO getSessions(DateTime start, DateTime end)
        {
            ApplicationSessionsTO result = new ApplicationSessionsTO();

            SqlConnection conn = new SqlConnection();
            SqlTransaction tx = null;
            try
            {
                conn = getConnection();

                tx = conn.BeginTransaction();
                SqlDataAdapter adapter = buildSelectSessionsAdapter(start, end, conn, tx);
                DataSet sessionData = new DataSet();
                int sessionCount = adapter.Fill(sessionData);

                tx.Commit();

                return new ApplicationSessionsTO(getApplicationSessions(sessionData));
            }
            catch (Exception exc)
            {
                if (tx != null && tx.Connection != null) // transaction connection should be null if completed
                {
                    tx.Rollback();
                }
                result.fault = new FaultTO(exc);
                return result;
            }
            finally
            {
                conn.Close();
                if (tx != null)
                {
                    tx.Dispose();
                }
            }
        }

        /// <summary>
        /// This is going to be commented out because it becomes unusably slow after a few hundred thousand unique sessions
        /// are created. Use the getSessions(int howMany) function instead to retrieve the latest howMany number of sessions
        /// </summary>
        /// <returns></returns>
        //public ApplicationSessionsTO getAllSessions()
        //{
        //    ApplicationSessionsTO result = new ApplicationSessionsTO();
        //    SqlConnection conn = new SqlConnection();
        //    SqlTransaction tx = null;
        //    try
        //    {
        //        conn = getConnection();

        //        tx = conn.BeginTransaction();
        //        SqlDataAdapter adapter = buildSelectSessionsAdapter(conn, tx);
        //        DataSet sessionData = new DataSet();
        //        int sessionCount = adapter.Fill(sessionData);

        //        adapter = buildSelectSessionRequestsAdapter(conn, tx);
        //        DataSet requestsData = new DataSet();
        //        int requestsCount = adapter.Fill(requestsData);
        //        tx.Commit();

        //        Dictionary<string, ApplicationSession> sessions = getApplicationSessions(sessionData);
        //        Stack<ApplicationRequest> requests = getApplicationRequests(requestsData);
        //        return new ApplicationSessionsTO(addRequestsToSessions(sessions, requests));
        //    }
        //    catch (Exception exc)
        //    {
        //        if (tx != null)
        //        {
        //            tx.Rollback();
        //        }
        //        result.fault = new FaultTO(exc);
        //        return null;
        //    }
        //    finally
        //    {
        //        conn.Close();
        //        if (tx != null)
        //        {
        //            tx.Dispose();
        //        }
        //    }
        //}

        SqlDataAdapter buildInsertRequestAdapter(ApplicationRequest request, SqlConnection conn, SqlTransaction tx)
        {
            SqlDataAdapter adapter = new SqlDataAdapter();
            adapter.InsertCommand = new SqlCommand();
            adapter.InsertCommand.Connection = conn;
            adapter.InsertCommand.Transaction = tx;
            adapter.InsertCommand.CommandText = "INSERT INTO dbo.MdwsSessionRequests " +
                "([ASP.NET_SessionId], URI, RequestTimestamp, ResponseTimestamp, RequestBody, ResponseBody) VALUES ('" +
                request.AspNetSessionId + "', '" + 
                request.Uri.LocalPath + "', " +
                "@requestTimestamp, @responseTimestamp, @RequestBody, @ResponseBody);";

            SqlParameter requestTimestampParam = new SqlParameter("@requestTimestamp", SqlDbType.DateTime);
            requestTimestampParam.Value = request.RequestTimestamp;

            SqlParameter responseTimestampParam = new SqlParameter("@responseTimestamp", SqlDbType.DateTime);
            responseTimestampParam.Value = request.ResponseTimestamp;

            SqlParameter reqestBodyParam = new SqlParameter("@RequestBody", SqlDbType.Text);
            reqestBodyParam.Value = String.IsNullOrEmpty(request.RequestBody) ? 
                (object)DBNull.Value : (object)request.RequestBody;

            SqlParameter responseBodyParam = new SqlParameter("@ResponseBody", SqlDbType.Text);
            responseBodyParam.Value = String.IsNullOrEmpty(request.ResponseBody) ? 
                (object)DBNull.Value : (object)request.ResponseBody;

            adapter.InsertCommand.Parameters.Add(requestTimestampParam);
            adapter.InsertCommand.Parameters.Add(responseTimestampParam);
            adapter.InsertCommand.Parameters.Add(reqestBodyParam);
            adapter.InsertCommand.Parameters.Add(responseBodyParam);
            return adapter;
        }

        SqlDataAdapter buildDeleteSessionAdapter(string sessionId, SqlConnection conn, SqlTransaction tx)
        {
            SqlDataAdapter adapter = new SqlDataAdapter();
            adapter.DeleteCommand = new SqlCommand();
            adapter.DeleteCommand.Connection = conn;
            adapter.DeleteCommand.Transaction = tx;
            adapter.DeleteCommand.CommandText = "DELETE FROM dbo.MdwsSessionRequests WHERE [ASP.NET_SessionId]=@SessionId; " +
                "DELETE FROM dbo.MdwsSessions WHERE [ASP.NET_SessionId]=@SessionId;";

            SqlParameter sessionIdParam = new SqlParameter("@SessionId", SqlDbType.Char, 24);
            sessionIdParam.Value = sessionId;

            adapter.DeleteCommand.Parameters.Add(sessionIdParam);
            return adapter;
        }

        SqlDataAdapter buildInsertSessionAdapter(ApplicationSession session, SqlConnection conn, SqlTransaction tx)
        {
            SqlDataAdapter adapter = new SqlDataAdapter();
            adapter.InsertCommand = new SqlCommand();
            adapter.InsertCommand.Connection = conn;
            adapter.InsertCommand.Transaction = tx;
            adapter.InsertCommand.CommandText = "INSERT INTO dbo.MdwsSessions " +
                "([ASP.NET_SessionId], IP, Start, [End], LocalhostName) VALUES ('" +
                session.AspNetSessionId + "', '" + session.RequestingIP + "', '" +
                session.Start.ToString() + "', '" + session.End.ToString() + "', '" +
                session.LocalhostName + "');";
            return adapter;
        }

        /// <summary>
        /// Build SQL adapter for selecting all MDWS sessions saved to database that where started between a given date range
        /// </summary>
        /// <param name="start">Start date</param>
        /// <param name="end">End date</param>
        /// <param name="conn">SQL Connection</param>
        /// <param name="tx">SQL Transaction</param>
        /// <returns>SQL adapter</returns>
        SqlDataAdapter buildSelectSessionsAdapter(DateTime start, DateTime end, SqlConnection conn, SqlTransaction tx)
        {
            SqlDataAdapter adapter = new SqlDataAdapter();
            adapter.SelectCommand = new SqlCommand();
            adapter.SelectCommand.Connection = conn;
            adapter.SelectCommand.Transaction = tx;
            adapter.SelectCommand.CommandText = "SELECT " +
                "MDWS.dbo.MdwsSessions.[ASP.NET_SessionId], " +
                "MDWS.dbo.MdwsSessions.IP, " +
                "MDWS.dbo.MdwsSessions.Start, " +
                "MDWS.dbo.MdwsSessions.[End], " +
                "MDWS.dbo.MdwsSessions.LocalhostName, " +
                "MDWS.dbo.MdwsSessionRequests.URI, " +
                "MDWS.dbo.MdwsSessionRequests.RequestTimestamp, " +
                "MDWS.dbo.MdwsSessionRequests.ResponseTimestamp, " +
                "MDWS.dbo.MdwsSessionRequests.RequestBody, " +
                "MDWS.dbo.MdwsSessionRequests.ResponseBody " +
                "FROM MDWS.dbo.MdwsSessions " +
                "LEFT JOIN dbo.MdwsSessionRequests ON dbo.MdwsSessions.[ASP.NET_SessionID]=dbo.MdwsSessionRequests.[ASP.NET_SessionId] " +
                "WHERE dbo.MdwsSessions.Start BETWEEN @Start AND @End;";

            SqlParameter startParam = new SqlParameter("@Start", SqlDbType.DateTime);
            startParam.Value = start;

            SqlParameter endParam = new SqlParameter("@End", SqlDbType.DateTime);
            endParam.Value = end;

            adapter.SelectCommand.Parameters.Add(startParam);
            adapter.SelectCommand.Parameters.Add(endParam);
            return adapter;
        }

        SqlDataAdapter buildSelectSessionRequestsAdapter(SqlConnection conn, SqlTransaction tx)
        {
            SqlDataAdapter adapter = new SqlDataAdapter();
            adapter.SelectCommand = new SqlCommand();
            adapter.SelectCommand.Connection = conn;
            adapter.SelectCommand.Transaction = tx;
            adapter.SelectCommand.CommandText = "SELECT * FROM dbo.MdwsSessionRequests;";
            return adapter;
        }

        SqlConnection getConnection()
        {
            SqlConnection conn = new SqlConnection(_connectionString);
            conn.Open();
            return conn;
        }

        ApplicationSessions getApplicationSessions(DataSet dataSet)
        {
            ApplicationSessions result = new ApplicationSessions();
            foreach (DataRow row in dataSet.Tables[0].Rows)
            {
                string sessionId = (string)row["ASP.NET_SessionId"];
                if (result.Sessions.ContainsKey(sessionId))
                {
                    addApplicationRequest(row, result.Sessions[sessionId]);
                }
                else
                {
                    string requestingIp = (string)row["IP"];
                    DateTime start = (DateTime)row["Start"];
                    DateTime end = (DateTime)row["End"];
                    string localhostName = (string)row["LocalhostName"];
                    ApplicationSession session = new ApplicationSession(sessionId, requestingIp, start, localhostName);
                    session.End = end;
                    result.Sessions.Add(sessionId, session);
                    addApplicationRequest(row, session);
                }
            }
            return result;
        }

        void addApplicationRequest(DataRow row, ApplicationSession parentSession)
        {
            string url = (string)row["URI"];
            DateTime requestTimestamp = (DateTime)row["RequestTimestamp"];
            DateTime responseTimestamp = (DateTime)row["ResponseTimestamp"];
            string requestBody = (row["RequestBody"] == DBNull.Value) ? "" : (string)row["RequestBody"];
            string responseBody = (row["ResponseBody"] == DBNull.Value) ? "" : (string)row["ResponseBody"];
            parentSession.Requests.Add(new ApplicationRequest
                (parentSession.AspNetSessionId, new Uri(url, UriKind.RelativeOrAbsolute), requestTimestamp, responseTimestamp, requestBody, responseBody));
        }

    }


}
