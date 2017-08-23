using System;
using System.Collections.Generic;
using System.Text;

namespace gov.va.medora.mdo.dao.hl7
{
    public class HL7Constants
    {
        public const char LLP_PREFIX = '\u000B';
        public const string LLP_SUFFIX = "\u001C\r";
        public const char SEGMENT_SEPARATOR = '\r';
        public const char EOB = '\u001C';
        public const string DEFAULT_DELIMITER = "^~\\&";
        public const char FIELD_SEPARATOR = '|';
    }
}
