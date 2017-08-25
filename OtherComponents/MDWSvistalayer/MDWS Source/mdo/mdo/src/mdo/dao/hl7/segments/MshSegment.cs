using System;
using System.Collections.Generic;
using System.Text;
using gov.va.medora.utils;

namespace gov.va.medora.mdo.dao.hl7.segments
{
    public class MshSegment
    {
        EncodingCharacters encChars = new EncodingCharacters();
        string sendingApp = "";
	    string sendingFacility = "";
	    string receivingApp = "";
	    string receivingFacility = "";
	    string timestamp = "";
	    string security = "";
	    string msgCode = "";
	    string eventTrigger = "";
	    string msgCtlId = "";
	    string processingId = "";
	    string versionId = "";
	    string sequenceNumber = "";
	    string continuationPointer = "";
	    string acceptAckType = "";
	    string appAckType = "";
	    string countryCode = "";

	    public MshSegment() {}
	
	    public MshSegment(string rawSegmentString) 
        {
		    parse(rawSegmentString);
	    }

        public string SendingApplication
        {
            get { return sendingApp; }
            set { sendingApp = value; }
        }

        public string SendingFacility
        {
            get { return sendingFacility; }
            set { sendingFacility = value; }
        }

        public string ReceivingApplication
        {
            get { return receivingApp; }
            set { receivingApp = value; }
        }

        public string ReceivingFacility
        {
            get { return receivingFacility; }
            set { receivingFacility = value; }
        }

        public string Timestamp
        {
            get { return timestamp; }
            set { timestamp = value; }
        }

        public string Security
        {
            get { return security; }
            set { security = value; }
        }

        public string MessageCode
        {
            get { return msgCode; }
            set { msgCode = value; }
        }

        public string EventTrigger
        {
            get { return eventTrigger; }
            set { eventTrigger = value; }
        }

        public string MessageControlID
        {
            get { return msgCtlId; }
            set { msgCtlId = value; }
        }

        public string ProcessingID
        {
            get { return processingId; }
            set { processingId = value; }
        }

        public string VersionID
        {
            get { return versionId; }
            set { versionId = value; }
        }

        public string SequenceNumber
        {
            get { return sequenceNumber; }
            set { sequenceNumber = value; }
        }

        public string ContinuationPointer
        {
            get { return continuationPointer; }
            set { continuationPointer = value; }
        }

        public string AcceptAckType
        {
            get { return acceptAckType; }
            set { acceptAckType = value; }
        }

        public string ApplicationAckType
        {
            get { return appAckType; }
            set { appAckType = value; }
        }

        public string CountryCode
        {
            get { return countryCode; }
            set { countryCode = value; }
        }

        public EncodingCharacters EncodingChars
        {
            get { return encChars; }
            set { encChars = value; }
        }

	    public virtual void parse(string rawSegmentString)
	    {
		    if (rawSegmentString.Substring(0,3) != "MSH")
		    {
			    throw new Exception("Invalid MSH segment: incorrect header");
		    }
    		
		    setSeparators(rawSegmentString);
    		
		    string[] flds = StringUtils.split(rawSegmentString,EncodingChars.FieldSeparator);
    		
		    if (flds.Length < 12)
		    {
			    throw new Exception("Invalid MSH segment: incorrect number of fields");
		    }
    		
		    SendingApplication = flds[2];
		    SendingFacility = flds[3];
		    ReceivingApplication = flds[4];
		    ReceivingFacility = flds[5];
    		
		    Timestamp = flds[6];
		    // TODO - Validate UTC timestamp

            Security = flds[7];
    		
		    if (StringUtils.isEmpty(flds[8])) 
            {
			    throw new Exception("Invalid MSH segment: missing message type");
		    }

		    string[] components = StringUtils.split(flds[8],EncodingChars.ComponentSeparator);

		    if (StringUtils.isEmpty(components[0])) 
            {
			    throw new Exception("Invalid MSH segment: incorrect message type fields");
		    }

		    MessageCode = components[0];

            if (components.Length > 1)
            {
                EventTrigger = components[1];
            }

            if (StringUtils.isEmpty(flds[9]))
            {
			    throw new Exception("Invalid MSH segment: missing message control ID");
		    }

		    MessageControlID = flds[9];

            if (StringUtils.isEmpty(flds[10]))
            {
			    throw new Exception("Invalid MSH segment: missing processing ID");
		    }

		    ProcessingID = flds[10];

            if (StringUtils.isEmpty(flds[11]))
            {
			    throw new Exception("Invalid MSH segment: missing version ID");
		    }

		    VersionID = flds[11];

            if (flds.Length > 12)
            {
                SequenceNumber = flds[12];
            }
            if (flds.Length > 13)
            {
                ContinuationPointer = flds[13];
            }
            if (flds.Length > 14)
            {
                AcceptAckType = flds[14];
            }
            if (flds.Length > 15)
            {
                ApplicationAckType = flds[15];
            }
            if (flds.Length > 16)
            {
                CountryCode = flds[16];
            }
	    }

        internal void setSeparators(string s)
        {
            encChars = new EncodingCharacters(s[3], s.Substring(4, 8));
        }

        public string toSegment()
        {
            string result = "MSH" +
                EncodingChars.FieldSeparator + EncodingChars.toString() +
                EncodingChars.FieldSeparator + SendingApplication +
                EncodingChars.FieldSeparator + SendingFacility +
                EncodingChars.FieldSeparator + ReceivingApplication +
                EncodingChars.FieldSeparator + ReceivingFacility +
                EncodingChars.FieldSeparator + DateTime.Now.ToString("yyyyMMddhhmmss") +
                EncodingChars.FieldSeparator + Security +
                EncodingChars.FieldSeparator + MessageCode + EncodingChars.ComponentSeparator + EventTrigger +
                EncodingChars.FieldSeparator + MessageControlID +
                EncodingChars.FieldSeparator + ProcessingID +
                EncodingChars.FieldSeparator + VersionID;
            return result + '\r';
        }
    }
}
