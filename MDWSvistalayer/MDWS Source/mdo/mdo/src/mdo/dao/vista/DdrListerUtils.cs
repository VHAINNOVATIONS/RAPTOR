using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;

namespace gov.va.medora.mdo.dao.vista
{
    public static class DdrListerUtils
    {
        public static String[] parseUnpackedResult(String ddrResponse, String requestFieldsString, String requestIdentifier)
        {
            String[] ddrResponseLines = gov.va.medora.utils.StringUtils.split(ddrResponse, gov.va.medora.utils.StringUtils.CRLF);

            IList<String> fields = getFieldsFromFieldsArg(requestFieldsString);
            fields = sortFieldsNumeric(fields); // due to comment on next line, need to sort these to mimic sorting of DDR LISTER
            //IList<String> fields = getFields(ddrResponseLines); // turns out the DDR results don't always return the complete fields list when the list is longer than 255 chars... need to use the DDR LISTER fields string
            IList<String> iens = getIens(ddrResponseLines);

            if (iens == null || iens.Count == 0)
            {
                return new String[0];
            }

            Dictionary<String, Dictionary<String, String>> valuesByIen = getValuesByIen(ddrResponseLines, iens, fields);
            Dictionary<String, String> identifierValuesByIen = getIdentifierPart(ddrResponseLines, iens, fields, requestIdentifier);

            return normalizeResults(requestFieldsString, iens, valuesByIen, identifierValuesByIen);
        }


        internal static IList<string> sortFieldsNumeric(IList<string> fields)
        {
            List<Decimal> fieldsNumeric = new List<Decimal>();
            IList<String> sortedStrings = new List<String>(fields.Count);
            for (int i = 0; i < fields.Count; i++)
            {
                fieldsNumeric.Add(Convert.ToDecimal(fields[i]));
                sortedStrings.Add(""); // so we can reference by index in a couple lines
            }
            fieldsNumeric.Sort();
            for (int i = 0; i < fieldsNumeric.Count; i++)
            {
                Int32 index = fieldsNumeric.IndexOf(Convert.ToDecimal(fields[i]));
                sortedStrings[index] = fields[i];
            }
            return sortedStrings;
        }

        // unpacked results do not return data in the same order it was requested sp we must manually build the results in the same order
        public static String[] normalizeResults(String requestFieldsString, IList<String> iens, Dictionary<String, Dictionary<String, String>> valuesByIen, Dictionary<String, String> identifierValuesByIen)
        {
            String[] lines = new String[iens.Count];
            requestFieldsString = requestFieldsString.Replace(";WID", ""); // remove "WID" from requested fields - always should be at end!!!
            String[] fields = gov.va.medora.utils.StringUtils.split(requestFieldsString, gov.va.medora.utils.StringUtils.SEMICOLON);
            
            StringBuilder lineBuilder = new StringBuilder();
            for (int i = 0; i < iens.Count; i++)
            {
                lineBuilder.Append(iens[i]);
                for (int j = 0; j < fields.Length; j++)
                {
                    lineBuilder.Append("^");
                    lineBuilder.Append(valuesByIen[iens[i]][gov.va.medora.utils.StringUtils.removeNonNumericChars(fields[j])]);
                }

                if (identifierValuesByIen != null && identifierValuesByIen.Count > 0)
                {
                    lineBuilder.Append("&#94;");
                    lineBuilder.Append(identifierValuesByIen[iens[i]]);
                }

                lines[i] = lineBuilder.ToString();

                lineBuilder.Clear();
            }

            return lines; // gov.va.medora.utils.StringUtils.split(lineBuilder.ToString(), gov.va.medora.utils.StringUtils.CRLF);
        }

        public static IList<String> getFields(String[] ddrResponseLines)
        {
            IList<String> result = new List<String>();

            String fieldsString = "";

            for (int i = 0; i < ddrResponseLines.Length; i++)
            {
                if (String.Equals(ddrResponseLines[i], "[MAP]"))
                {
                    fieldsString = ddrResponseLines[i + 1];
                    break;
                }
            }

            String[] fields = gov.va.medora.utils.StringUtils.split(fieldsString, '^');

            for (int i = 0; i < fields.Length; i++)
            {
                String currentField = gov.va.medora.utils.StringUtils.removeNonNumericChars(fields[i]);
                result.Add(currentField);
            }

            return result;
        }

        public static IList<String> getFieldsFromFieldsArg(String fieldsArg)
        {
            IList<String> result = new List<String>();

            String[] fields = fieldsArg.Split(new char[] { ';' });

            for (int i = 0; i < fields.Length; i++)
            {
                String currentField = gov.va.medora.utils.StringUtils.removeNonNumericChars(fields[i]);
                result.Add(currentField);
            }

            return result;
        }

        public static IList<String> getIens(String[] ddrResponseLines)
        {
            IList<String> result = new List<String>();

            for (int i = 0; i < ddrResponseLines.Length; i++)
            {
                if (String.Equals(ddrResponseLines[i], "BEGIN_IENs"))
                {
                    i++;
                    while (!String.Equals(ddrResponseLines[i], "END_IENs"))
                    {
                        result.Add(ddrResponseLines[i]);
                        i++;
                    }
                    break;
                }
            }

            return result;
        }

        public static Dictionary<String, Dictionary<String, String>> getValuesByIen(String[] ddrResponseLines, IList<String> iens, IList<String> fields)
        {
            StringBuilder sb = new StringBuilder();
            for (int i = 0; i < ddrResponseLines.Length; i++)
            {
                sb.AppendLine(ddrResponseLines[i]);
            }
            
            Dictionary<String, Dictionary<String, String>> result = new Dictionary<string, Dictionary<string, string>>();

            int dataStartIndex = iens.Count;
            for (int i = dataStartIndex; i < ddrResponseLines.Length; i++)
            {
                if (String.Equals(ddrResponseLines[i], "BEGIN_IDVALUES"))
                {
                    dataStartIndex = i + 1;
                    break;
                }
            }

            for (int i = 0; i < iens.Count; i++)
            {
                result.Add(iens[i], new Dictionary<string, string>());
            }

            int ienIndex = 0;
            int approxEndIndex = (iens.Count * fields.Count) + iens.Count; // this slight optimization should make it more efficient to check for the end of the results
            for (int i = dataStartIndex; i < ddrResponseLines.Length; i++) // might be fetching several thousand records in which case we'd unneccessarily examine several thousand rows to find start of data - better to just skip # of lines we know is safe
            {
                for (int j = 0; j < fields.Count; j++)
                {
                    result[iens[ienIndex]].Add(fields[j], ddrResponseLines[i]);
                    i++;
                }
                i--; // because we're incrementing i in the j loop above, we go 1 past the last field with each iteration - just subtract the last one back out 
                ienIndex++;

                if (i > approxEndIndex) // should be more efficient than a string comparison for each IEN
                {
                    if (String.Equals(ddrResponseLines[i + 1], "END_IDVALUES"))
                    {
                        break;
                    }
                }
            }

            return result;
        }

        public static Dictionary<String, String> getIdentifierPart(String[] ddrResponseLines, IList<String> iens, IList<String> fields, String identifierString)
        {
            Dictionary<String, String> result = new Dictionary<string, string>();
            if (String.IsNullOrEmpty(identifierString))
            {
                return result;
            }

            int approxStartIndex = iens.Count + (fields.Count * iens.Count); // a line for each IEN and then a line for each field of each record gets us close to start of WID
            int startIndex = approxStartIndex;
            for (int i = startIndex; i < ddrResponseLines.Length; i++)
            {
                if (String.Equals(ddrResponseLines[i], "BEGIN_WIDVALUES"))
                {
                    startIndex = i + 1;
                    break;
                }
            }

            if (startIndex == approxStartIndex || startIndex >= ddrResponseLines.Length || startIndex < 0) // protect against goofy start index
            {
                // i don't think we want to throw an exception - maybe the identifier param wasn't returning any data... - we'll just return without issue and let the client decide
                //throw new mdo.exceptions.MdoException(exceptions.MdoExceptionCode.DATA_UNEXPECTED_FORMAT, "DDR results did not include identifier piece");
                return result;
            }

            //int approxEndIndex = (3 * iens.Count) + approxStartIndex; // 3 lines for each identifier + start index
            for (int i = startIndex; i < ddrResponseLines.Length; i++)
            {
                String headerLineIen = gov.va.medora.utils.StringUtils.piece(ddrResponseLines[i], "^", 2);
                String headerLineNumLines = gov.va.medora.utils.StringUtils.piece(ddrResponseLines[i], "^", 3);
                int numOfIdentifierLines = Convert.ToInt32(headerLineNumLines);

                for (int j = 0; j < numOfIdentifierLines; j++)
                {
                    if (!result.ContainsKey(headerLineIen)) 
                    {
                        result.Add(headerLineIen, ddrResponseLines[i + j + 1]); // +1 to move to next line after header
                    }
                    else // this IEN has multiple lines - need to 'massage' lines in to a single record like a packed output would do
                    {
                        result[headerLineIen] = String.Concat(result[headerLineIen], "~", ddrResponseLines[i + j + 1]);
                    }
                }

                i += numOfIdentifierLines;

                if (String.Equals(ddrResponseLines[i], "END_WIDVALUES"))
                {
                    break;
                }
            }

            return result;
        }
    }
}
