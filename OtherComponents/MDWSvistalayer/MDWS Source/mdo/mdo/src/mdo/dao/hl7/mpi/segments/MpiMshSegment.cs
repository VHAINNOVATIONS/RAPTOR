using System;
using System.Collections.Generic;
using System.Text;
using gov.va.medora.utils;
using gov.va.medora.mdo.dao.hl7.segments;

namespace gov.va.medora.mdo.dao.hl7.mpi
{
    public class MpiMshSegment : MshSegment
    {
        // This method is necessary because MPI does not implement all the 
        // compulsory fields.  So much for compulsory.
        public override void parse(string rawSegmentString)
        {
            if (rawSegmentString.Substring(0, 3) != "MSH")
            {
                throw new Exception("Invalid MSH segment: incorrect header");
            }

            setSeparators(rawSegmentString);

            string[] flds = StringUtils.split(rawSegmentString, EncodingChars.FieldSeparator.ToString());

            if (flds.Length < 7)
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

            string[] components = StringUtils.split(flds[8], EncodingChars.ComponentSeparator);

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

    }
}
