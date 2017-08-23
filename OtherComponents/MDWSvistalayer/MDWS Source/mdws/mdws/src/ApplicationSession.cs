using System;
using System.Collections.Generic;
using System.Configuration;
using System.Web;
using System.Net;
using gov.va.medora.mdws.dao.sql;
using gov.va.medora.mdws.dto;
using System.Text;
using gov.va.medora.mdws.conf;

namespace gov.va.medora.mdws
{
    public enum ApplicationSessionsLogLevel
    {
        info, 
        debug
    }

    #region ApplicationSessions Class
    public class ApplicationSessions
    {
        ApplicationSessionsLogLevel _logLevel;
        DateTime _start;
        Dictionary<string, ApplicationSession> _sessions;

        public DateTime Start
        {
            get { return _start; }
            set { _start = value; }
        }
        public Dictionary<string, ApplicationSession> Sessions
        {
            get { return _sessions; }
            set { _sessions = value; }
        }
        public ApplicationSessionsLogLevel LogLevel
        {
            get { return _logLevel; }
            set { _logLevel = value; }
        }


        public ApplicationSessions()
        {
            _logLevel = ApplicationSessionsLogLevel.info;
            _sessions = new Dictionary<string, ApplicationSession>();
            _configurationSettings = new MdwsConfiguration();
        }

        MdwsConfiguration _configurationSettings;
        public MdwsConfiguration ConfigurationSettings
        {
            get 
            {
                if (_configurationSettings == null)
                {
                    _configurationSettings = new MdwsConfiguration();
                }
                return _configurationSettings; 
            }
            set { _configurationSettings = value; }
        }

        public override string ToString()
        {
            if (_sessions != null && _sessions.Count > 0)
            {
                StringBuilder sb = new StringBuilder("MDWS Sessions (" + _start.ToString() + " to " + DateTime.Now.ToString() + ")" + Environment.NewLine);
                foreach (ApplicationSession session in _sessions.Values)
                {
                    sb.AppendLine(session.ToString());
                }
                return sb.ToString();
            }
            else
            {
                return "No current MDWS sessions! (" + _start.ToString() + " to " + DateTime.Now.ToString() + ")" + Environment.NewLine;
            }
        }

        /// <summary>
        /// Walk through the ApplicationSession objects and compress the ApplicationRequest.RequestBody and ApplicationRequest.ResponseBody
        /// properties setting ApplicationRequest.CompressedRequest and Application.CompressedResponse to those values. Also sets RequestBody and ResponseBody to null
        /// </summary>
        public void compressApplicationSessions()
        {
            if (_sessions != null && _sessions.Count > 0)
            {
                gov.va.medora.utils.Compression compressor = new gov.va.medora.utils.Compression();
                foreach (ApplicationSession session in _sessions.Values)
                {
                    if (session.Requests != null && session.Requests.Count > 0)
                    {
                        foreach (ApplicationRequest request in session.Requests)
                        {
                            if (!String.IsNullOrEmpty(request.RequestBody))
                            {
                                request.CompressedRequest = compressor.compress(request.RequestBody);
                                request.RequestBody = "";
                            }
                            if (!String.IsNullOrEmpty(request.ResponseBody))
                            {
                                request.CompressedResponse = compressor.compress(request.ResponseBody);
                                request.ResponseBody = "";
                            }
                        }
                    }
                }
            }
        }

        /// <summary>
        /// Walk through the ApplicationSession objects and decompress the ApplicationRequest.CompressedRequest and ApplicationRequest.CompressedResponse
        /// properties setting ApplicationRequest.RequestBody and Application.ResponseBody to those values. Also sets CompressedRequest and CompressedResponse to null
        /// </summary>
        public void decompressApplicationSessions()
        {
            if (_sessions != null && _sessions.Count > 0)
            {
                gov.va.medora.utils.Compression compressor = new gov.va.medora.utils.Compression();
                foreach (ApplicationSession session in _sessions.Values)
                {
                    if (session.Requests != null && session.Requests.Count > 0)
                    {
                        foreach (ApplicationRequest request in session.Requests)
                        {
                            if (request.CompressedRequest != null && request.CompressedRequest.Length > 0)
                            {
                                request.RequestBody = compressor.decompress(request.CompressedRequest) as string;
                                request.CompressedRequest = null;
                            }
                            if (request.CompressedResponse != null && request.CompressedResponse.Length > 0)
                            {
                                request.ResponseBody = compressor.decompress(request.CompressedResponse) as string;
                                request.CompressedResponse = null;
                            }
                        }
                    }
                }
            }
        }
    }
    #endregion

    #region ApplicationSession Class
    public class ApplicationSession
    {
        #region Setters and Getters
        string _aspNetSessionId;
        public string AspNetSessionId
        {
            get { return _aspNetSessionId; }
            set { _aspNetSessionId = value; }
        }

        string _requestingIP;
        public string RequestingIP
        {
            get { return _requestingIP; }
            set { _requestingIP = value; }
        }

        DateTime _start;
        public DateTime Start
        {
            get { return _start; }
            set { _start = value; }
        }

        DateTime _end;
        public DateTime End
        {
            get { return _end; }
            set { _end = value; }
        }

        List<ApplicationRequest> _requests;
        public List<ApplicationRequest> Requests
        {
            get { return _requests; }
            set { _requests = value; }
        }

        string _localhostName;
        public string LocalhostName
        {
            get { return _localhostName; }
            set { _localhostName = value; }
        }
        #endregion

        public ApplicationSession(string sessionId, string requestingIp, DateTime start)
        {
            _aspNetSessionId = sessionId;
            _requestingIP = requestingIp;
            _start = start;
            _requests = new List<ApplicationRequest>();
        }

        public ApplicationSession(string sessionId, string requestingIp, DateTime start, string localhostName)
        {
            _aspNetSessionId = sessionId;
            _requestingIP = requestingIp;
            _start = start;
            _requests = new List<ApplicationRequest>();
            _localhostName = localhostName;
        }

        /// <summary>
        /// Builds a string representation of an Application Session object
        /// </summary>
        /// <returns>
        /// MDWS Session: session ID
        /// Requesting IP
        /// Session Start Time
        /// Session End Time
        /// Session Requests
        /// </returns>
        public override string ToString()
        {
            StringBuilder sb = new StringBuilder("MDWS Session: " + _aspNetSessionId);
            sb.AppendLine();
            sb.AppendLine("Requesting IP: " + _requestingIP);
            sb.AppendLine("Session Started: " + _start.ToString());
            sb.AppendLine("Session Ended: " + _end.ToString());
            if (_requests != null && _requests.Count > 0)
            {
                sb.AppendLine("Session Requests:");
                foreach (ApplicationRequest request in _requests)
                {
                    sb.AppendLine(request.ToString());
                }
            }
            return sb.ToString();
        }

    }
    #endregion

    #region ApplicationRequest Class
    public class ApplicationRequest
    {
        #region Setters and Getters
        string _aspNetSessionId;

        public string AspNetSessionId
        {
          get { return _aspNetSessionId; }
          set { _aspNetSessionId = value; }
        }

        Uri _uri;

        public Uri Uri
        {
          get { return _uri; }
          set { _uri = value; }
        }

        DateTime _requestTimestamp;

        public DateTime RequestTimestamp
        {
            get { return _requestTimestamp; }
            set { _requestTimestamp = value; }
        }

        DateTime _responseTimestamp;

        public DateTime ResponseTimestamp
        {
            get { return _responseTimestamp; }
            set { _responseTimestamp = value; }
        }

        string _requestBody;

        public string RequestBody
        {
            get 
            {
                //if (CompressedRequest != null && CompressedRequest.Length > 0 && String.IsNullOrEmpty(_responseBody))
                //{
                //    return _compressor.decompress(CompressedRequest) as string;
                //}
                return _requestBody; 
            }
            set { _requestBody = value; }
        }

        string _responseBody;

        public string ResponseBody
        {
            // convenience getter - if data is available in the compressed properties and the string property is empty
            // then assume the compressed  property has been set and holds the string data
            get 
            {
                //if (CompressedResponse != null && CompressedResponse.Length > 0 && String.IsNullOrEmpty(_responseBody))
                //{
                //    return _compressor.decompress(CompressedResponse) as string;
                //}
                return _responseBody; 
            }
            set { _responseBody = value; }
        }

        /// <summary>
        /// The RequestBody in compressed format
        /// </summary>
        public byte[] CompressedRequest { get; set; }
        /// <summary>
        /// The ResponseBody in compressed format
        /// </summary>
        public byte[] CompressedResponse { get; set; }
        #endregion

        public ApplicationRequest() { /* empty constructor */ }

        public ApplicationRequest(string sessionId, Uri uri)
        {
            _aspNetSessionId = sessionId;
            _uri = uri;
        }

        public ApplicationRequest(string sessionId, Uri uri, DateTime requestTimestamp, DateTime responseTimestamp, string requestBody, string responseBody)
        {
            _aspNetSessionId = sessionId;
            _uri = uri;
            _requestTimestamp = requestTimestamp;
            _responseTimestamp = responseTimestamp;
            _requestBody = requestBody;
            _responseBody = responseBody;
        }

        /// <summary>
        /// Builds a string representaion of the Application Request object
        /// </summary>
        /// <returns>
        /// SOAP method
        /// Request Body:
        /// <soap>...</soap>
        /// Response Body:
        /// <soap>...</soap>
        /// </returns>
        public override string ToString()
        {
            StringBuilder sb = new StringBuilder(_uri.ToString());
            if(!String.IsNullOrEmpty(_requestBody))
            {
                sb.Append(Environment.NewLine + "Request Body:" + Environment.NewLine + _requestBody);
            }
            if(!String.IsNullOrEmpty(_responseBody))
            {
                sb.Append(Environment.NewLine + "Response Body:" + Environment.NewLine + _responseBody);
            }
            return sb.ToString();
        }
    }
    #endregion

    #region Application Session TO Objects

    #region ApplicationSessionsTO Object
    public class ApplicationSessionsTO : AbstractTO
    {
        public ApplicationSessionTO[] sessions;

        public ApplicationSessionsTO() { }

        public ApplicationSessionsTO(ApplicationSessions sessions)
        {
            if (sessions != null)
            {
                setSessions(sessions.Sessions);
            }
        }

        public ApplicationSessionsTO(Dictionary<string, ApplicationSession> sessions)
        {
            setSessions(sessions);
        }

        void setSessions(Dictionary<string, ApplicationSession> sessions)
        {
            if (sessions != null)
            {
                this.sessions = new ApplicationSessionTO[sessions.Count];
                string[] keys = new string[sessions.Count];
                sessions.Keys.CopyTo(keys, 0);
                for (int i = 0; i < sessions.Count; i++)
                {
                    this.sessions[i] = new ApplicationSessionTO(sessions[keys[i]]);
                }
            }
        }
    }
    #endregion

    #region ApplicationSessionTO Object
    public class ApplicationSessionTO : AbstractTO
    {
        public string sessionId;
        public string requestingIp;
        public string start;
        public string end;
        public ApplicationRequestTO[] requests;
        public string localhostName;

        public ApplicationSessionTO() { }

        public ApplicationSessionTO(ApplicationSession session)
        {
            this.sessionId = session.AspNetSessionId;
            this.requestingIp = session.RequestingIP;
            this.start = session.Start.ToString();
            this.end = session.End.ToString();
            if (session.Requests != null)
            {
                this.requests = new ApplicationRequestTO[session.Requests.Count];
                for (int i = 0; i < session.Requests.Count; i++)
                {
                    this.requests[i] = new ApplicationRequestTO(session.Requests[i]);
                }
            }
            this.localhostName = session.LocalhostName;
        }

        public ApplicationSessionTO(string sessionId, string requestingIp, DateTime start, DateTime end, List<ApplicationRequest> requests)
        {
            this.sessionId = sessionId;
            this.requestingIp = requestingIp;
            this.start = start.ToString();
            this.end = end.ToString();
            if (requests != null)
            {
                this.requests = new ApplicationRequestTO[requests.Count];
                for (int i = 0; i < requests.Count; i++)
                {
                    this.requests[i] = new ApplicationRequestTO(requests[i]);
                }
            }
        }

        public ApplicationSessionTO(string sessionId, string requestingIp, DateTime start, DateTime end, List<ApplicationRequest> requests, string localhostName)
        {
            this.sessionId = sessionId;
            this.requestingIp = requestingIp;
            this.start = start.ToString();
            this.end = end.ToString();
            if (requests != null)
            {
                this.requests = new ApplicationRequestTO[requests.Count];
                for (int i = 0; i < requests.Count; i++)
                {
                    this.requests[i] = new ApplicationRequestTO(requests[i]);
                }
            }
            this.localhostName = localhostName;
        }

    }
    #endregion

    #region ApplicationRequestTO Object
    public class ApplicationRequestTO : AbstractTO
    {
        public string sessionId;
        public string uri;
        public string requestTimestamp;
        public string responseTimestamp;
        public string requestBody;
        public string responseBody;

        public ApplicationRequestTO() { }

        public ApplicationRequestTO(ApplicationRequest request)
        {
            this.sessionId = request.AspNetSessionId;
            this.uri = request.Uri.ToString();
            this.requestTimestamp = request.RequestTimestamp.ToString();
            this.responseTimestamp = request.ResponseTimestamp.ToString();
            this.requestBody = request.RequestBody;
            this.responseBody = request.ResponseBody;
        }

        public ApplicationRequestTO(string sessionId, Uri uri, DateTime requestTimestamp, DateTime responseTimestamp)
        {
            this.sessionId = sessionId;
            this.uri = uri.AbsoluteUri;
            this.requestTimestamp = requestTimestamp.ToString();
            this.responseTimestamp = responseTimestamp.ToString();
        }
    }
    #endregion

    #endregion


}
