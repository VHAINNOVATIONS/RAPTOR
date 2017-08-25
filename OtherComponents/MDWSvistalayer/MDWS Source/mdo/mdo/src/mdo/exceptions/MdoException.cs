using System;
using System.Collections.Generic;
using System.Runtime.Serialization;
using System.Text;
using System.Collections;
using gov.va.medora.mdo.src.mdo;

namespace gov.va.medora.mdo.exceptions
{
    [Serializable]
    public class MdoException : ApplicationException
    {
        #region Private Properties
        MdoExceptionCode _errorCode;
        string _requestString;
        string _responseString;
        #endregion

        #region Constructors
        /// <summary>
        /// Base class for all MdoException classes - extends ApplicationException
        /// </summary>
        public MdoException() : base() { }

        /// <summary>
        /// Base class for all MdoException classes - extends ApplicationException
        /// </summary>
        /// <param name="message">Exception message</param>
        public MdoException(string message) : base(message) { }

        /// <summary>
        /// Base class for all MdoException classes - extends ApplicationException
        /// </summary>
        /// <param name="message"></param>
        /// <param name="inner"></param>
        public MdoException(string message, Exception inner) : base(message, inner) { }

        /// <summary>
        /// Base class for all MdoException classes - extends ApplicationException
        /// </summary>
        /// <param name="info">Data needed to serialize and deserialize and object</param>
        /// <param name="context"></param>
        public MdoException(SerializationInfo info, StreamingContext context) : base(info, context) { }

        /// <summary>
        /// Base class for all MdoException classes - extends ApplicationException
        /// </summary>
        /// <param name="code">MDO exception code</param>
        public MdoException(MdoExceptionCode code) : base()
        {
            this._errorCode = code;
        }

        /// <summary>
        /// Base class for all MdoException classes - extends ApplicationException
        /// </summary>
        /// <param name="code">MDO exception code</param>
        /// <param name="message">Exception message</param>
        public MdoException(MdoExceptionCode code, string message) : base(message) 
        {
            this._errorCode = code;
        }

        /// <summary>
        /// Base class for all MdoException classes - extends ApplicationException
        /// </summary>
        /// <param name="code">MDO exception code - sets code text constant as the exception text</param>
        /// <param name="inner">Inner exception</param>
        public MdoException(MdoExceptionCode code, Exception inner)
            : base(Enum.GetName(typeof(MdoExceptionCode), code), inner)
        {
            this._errorCode = code;
        }

        /// <summary>
        /// Base class for all MdoException classes - extends ApplicationException
        /// </summary>
        /// <param name="requestString">The request string build as part of a request for data</param>
        /// <param name="responseString">The response string received from a request made for data</param>
        public MdoException(string requestString, string responseString)
            : base()
        {
            _requestString = requestString;
            _responseString = responseString;
        }

        /// <summary>
        /// Base class for all MdoException classes - extends ApplicationException
        /// </summary>
        /// <param name="query">The orginating query</param>
        /// <param name="responseString">The response string received from a request made for data</param>
        /// <param name="code">MDO exception code - sets code text constant as the exception text</param>
        /// <param name="inner">Inner Exception</param>
        public MdoException(MdoQuery query, string responseString, MdoExceptionCode code, Exception inner)
            : base(inner.Message, inner)
        {
            _errorCode = code;
            if(null != query)
                _requestString = query.buildMessage();
            _responseString = responseString;
        }


        /// <summary>
        /// Base class for all MdoException classes - extends ApplicationException
        /// </summary>
        /// <param name="message">The orginating exception message</param>
        /// <param name="requestString">The request string build as part of a request for data</param>
        /// <param name="responseString">The response string received from a request made for data</param>
        /// <param name="code">MDO exception code - sets code text constant as the exception text</param>
        /// <param name="inner">Inner Exception</param>
        public MdoException(string requestString, string responseString, MdoExceptionCode code, Exception inner)
            : base(inner.Message, inner)
        {
            _errorCode = code;
            _requestString = requestString;
            _responseString = responseString;
        }

        /// <summary>
        /// Base class for all MdoException classes - extends ApplicationException
        /// </summary>
        /// <param name="query">The orginating exception message</param>
        /// <param name="requestString">The request string build as part of a request for data</param>
        /// <param name="responseString">The response string received from a request made for data</param>
        public MdoException(MdoQuery query, string responseString, Exception inner)
           : base(inner.Message, inner)
        {
            if (query != null)
            {
                _requestString = query.buildMessage();
            }
            _responseString = responseString;
        }
    
        /// <summary>
        /// Base class for all MdoException classes - extends ApplicationException
        /// </summary>
        /// <param name="message">The orginating exception message</param>
        /// <param name="requestString">The request string build as part of a request for data</param>
        /// <param name="responseString">The response string received from a request made for data</param>
        public MdoException(string message, string requestString, string responseString)
            : base(message)
        {
            _requestString = requestString;
            _responseString = responseString;
        }
        
        /// <summary>
        /// Base class for all MdoException classes - extends ApplicationException
        /// </summary>
        /// <param name="requestString">The request string build as part of a request for data</param>
        /// <param name="responseString">The response string received from a request made for data</param>
        /// <param name="inner">Inner Exception</param>
        public MdoException(string requestString, string responseString, Exception inner) : 
            base(inner.Message, inner)
        {
            _requestString = requestString;
            _responseString = responseString;
        }
        #endregion

        #region Setters and Getters
        public MdoExceptionCode ErrorCode
        {
            get { return _errorCode; }
            set { _errorCode = value; }
        }

        public string RequestString
        {
            get { return _requestString; }
            set { _requestString = value; }
        }

        public string ResponseString
        {
            get { return _responseString; }
            set { _responseString = value; }
        }
        #endregion

        /// <summary>
        /// Builds a string from the exception's properties and then
        /// iterates through each inner exception and appends it's properties
        /// to the string
        /// </summary>
        /// <returns>A string representation of the exception and all inner exception objects</returns>
        public override string ToString()
        {
            Exception current = this;
            StringBuilder sb = new StringBuilder();
            sb.Append("*** Begin Exception String ***" + Environment.NewLine);
            while (current != null)
            {
                sb.Append(getExceptionLevelString(current));
                current = current.InnerException;
            }
            sb.Append("*** End Exception String ***" + Environment.NewLine);
            return sb.ToString();
        }

        string getExceptionLevelString(Exception e)
        {
            StringBuilder sb = new StringBuilder();
            sb.AppendLine("Exception Type: " + e.GetType().Name);
            // if the current exception type is MdoExceotion, enumerate through the interesting properties and add them to the string
            if (e is MdoException)
            {
                MdoException exc = e as MdoException;
                if (exc.ErrorCode != null)
                {
                    sb.AppendLine("Error Code: " + exc.ErrorCode);
                    sb.AppendLine("Error Code Value: " + Enum.GetName(typeof(MdoExceptionCode), exc.ErrorCode));
                }
                if (!String.IsNullOrEmpty(exc.RequestString))
                {
                    sb.AppendLine("Request String: " + exc.RequestString);
                }
                if (!String.IsNullOrEmpty(exc.ResponseString))
                {
                    sb.AppendLine("Response String: " + exc.ResponseString);
                }
            }
            sb.AppendLine("Help Message: " + e.HelpLink);
            sb.AppendLine("Message: " + e.Message);
            sb.AppendLine("Source: " + e.Source);
            sb.AppendLine("Stack Trace: " + e.StackTrace);
            sb.AppendLine("Target Site: " + e.TargetSite);
            if(e.Data != null && e.Data.Count > 0)
            {
                sb.AppendLine("Exception Data:");
                foreach(DictionaryEntry de in e.Data)
                {
                    sb.AppendLine("\t" + de.Key + " : " + de.Value);
                }
            }
            return sb.ToString();
        }
    }

    public enum MdoExceptionCode
    {
        FILE_NON_SPECIFIC_IO_ERROR = 1000,
        FILE_CANT_OPEN_SITES_FILE,
        FILE_CANT_OPEN_VISTA_FILES_FILE,

        NETWORK_NON_SPECIFIC_ERROR = 2000,
        NETWORK_CANT_CONNECT_TO_VISTA,
        NETWORK_CANT_CONNECT_TO_SQL,
        NETWORK_CANT_CONNECT_TO_DATASOURCE,

        DATA_NON_SPECIFIC_DATA_RECEIVED_ERROR = 3000,
        DATA_UNEXPECTED_FORMAT,
        DATA_MISSING_REQUIRED,
        DATA_INVALID_MULTIPLE_RECORDS,
        DATA_INVALID,
        DATA_NO_RECORD_FOR_ID,
        DATA_LOCKING_ERROR,

        ARGUMENT_NON_SPECIFIC_ERROR = 4000,
        ARGUMENT_NULL,
        ARGUMENT_NULL_SSN,
        ARGUMENT_NULL_USER_ID,
        ARGUMENT_NULL_PATIENT_ID,
        ARGUMENT_INVALID,
        ARGUMENT_INVALID_NUMERIC_REQUIRED,
        ARGUMENT_DATE_FORMAT,

        PERMISSION_NON_SPECIFIC_ERROR = 5000,
        PERMISSION_LOCKED,
        PERMISSION_NOT_FOUND,

        USAGE_NON_SPECIFIC_ERROR = 6000,
        USAGE_NO_CONNECTION,

        VISTA_NON_SPECIFIC_ERROR = 7000,
        VISTA_DATA_ERROR,
        VISTA_FAULT,

        REQUEST_RESPONSE_ERROR = 8000,

        DATA_SOURCE_NON_SPECIFIC_ERROR = 3500,
        DATA_SOURCE_NULL,
        DATA_SOURCE_MISSING_CXN_STRING,
        DATA_SOURCE_INVALID,

    }

    public class NotConnectedException : MdoException
    {
        public NotConnectedException() : base(MdoExceptionCode.USAGE_NO_CONNECTION, "Unable to instantiate DAO: not connected") { }
    }

    public class NoSuchRecordException : MdoException
    {
        public NoSuchRecordException(string recordId) : base(MdoExceptionCode.DATA_NO_RECORD_FOR_ID, "No record for ID: " + recordId) { }
    }

    public class InvalidlyFormedRecordIdException : MdoException
    {
        public InvalidlyFormedRecordIdException(string recordId) : base(MdoExceptionCode.ARGUMENT_INVALID, "Invalidly formed record ID: " + recordId) { }
    }

    public class InvalidDateRangeException : MdoException
    {
        public InvalidDateRangeException() : base(MdoExceptionCode.ARGUMENT_DATE_FORMAT, "Invalid date range") { }
    }

    public class NullOrEmptyParamException : MdoException
    {
        public NullOrEmptyParamException(string param) : base(MdoExceptionCode.ARGUMENT_NULL, "Null or empty input parameter: " + param) { }
    }

    public class RecordLockingException : MdoException
    {
        public RecordLockingException(string msg) : base(MdoExceptionCode.DATA_LOCKING_ERROR, "locking error: " + msg) { }
    }
}
