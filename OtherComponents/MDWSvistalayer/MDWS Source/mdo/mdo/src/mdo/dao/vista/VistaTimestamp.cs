using System;
using System.Collections.Generic;
using System.Text;
using gov.va.medora.utils;

namespace gov.va.medora.mdo.dao.vista
{
    public class VistaTimestamp
    {
        const int yrIdx = 0;
        const int yrLth = 3;
        const int moIdx = 3;
        const int moLth = 2;
        const int dyIdx = 5;
        const int dyLth = 2;
        const int dotIdx = 7;
        const int dotLth = 1;
        const int hrIdx = 8;
        const int hrLth = 2;
        const int mnIdx = 10;
        const int mnLth = 2;
        const int scIdx = 12;
        const int scLth = 2;

        public static String getDatePart(String ts)
        {
            return padZeroes(ts).Substring(yrIdx,yrLth);
        }

        public static String getTimePart(String ts)
        {
            return padZeroes(ts).Substring(hrIdx);
        }

        public static int getYear(String ts)
        {
            return Convert.ToInt32(padZeroes(ts).Substring(yrIdx,yrLth)) + 1700;
        }

        public static int getMonth(String ts)
        {
            return Convert.ToInt32(padZeroes(ts).Substring(moIdx,moLth));
        }

        public static int getDay(String ts)
        {
            return Convert.ToInt32(padZeroes(ts).Substring(dyIdx,dyLth));
        }

        public static int getHour(String ts)
        {
            return Convert.ToInt32(padZeroes(ts).Substring(hrIdx,hrLth));
        }

        public static int getMinute(String ts)
        {
            return Convert.ToInt32(padZeroes(ts).Substring(mnIdx,mnLth));
        }

        public static int getSecond(String ts)
        {
            return Convert.ToInt32(padZeroes(ts).Substring(scIdx));
        }

        public static String padZeroes(String ts)
        {
            if (ts == "*SENSITIVE*")
            {
                return ts;
            }
            if (ts.IndexOf("@") != -1)
            {
                ts = ts.Replace('@','.');
            }
            if (ts.IndexOf(".") != -1)
            {
                string[] parts = StringUtils.split(ts, ".");
                ts = parts[0] + "0000000".Substring(0, 7 - parts[0].Length) + '.' +
                     parts[1] + "000000".Substring(0, 6 - parts[1].Length);
            }
            else
            {
                ts = ts + "0000000".Substring(0,7-ts.Length) + ".000000";
            }
            return ts;
        }

        public static bool isValid(String ts)
        {
            try
            {
                ts = padZeroes(ts);
                int yr = getYear(ts);
                if (yr < 1900)
                {
                    return false;
                }
                int mo = getMonth(ts);
                if (mo > 12)
                {
                    return false;
                }
                int dy = getDay(ts);
                if (mo > 0 && dy > 0 && !DateUtils.isValidDay(yr, mo, dy))
                {
                    return false;
                }
                if (getHour(ts) > 23)
                {
                    return false;
                }
                if (getMinute(ts) > 59)
                {
                    return false;
                }
                if (getSecond(ts) > 59)
                {
                    return false;
                }
            }
            catch
            {
                return false;
            }
            return true;
        }

        public static String corrected(String ts)
        {
            if (ts == "*SENSITIVE*")
            {
                return ts;
            }
            ts = padZeroes(ts);
            int yr = getYear(ts);
            if (yr < 1900)
            {
                return "";
            }
            int mo = getMonth(ts);
            if (mo == -1 || mo > 12)
            {
                ts = ts.Substring(yrIdx,yrLth) + "00" + ts.Substring(dyIdx);
                mo = 0;
            }
            int dy = getDay(ts);
            if (dy == -1 || !DateUtils.isValidDay(yr,mo,dy))
            {
                ts = ts.Substring(yrIdx,yrLth+moLth) + "00" + ts.Substring(dotIdx);
                dy = 0;
            }
            int hr = getHour(ts);
            if (hr == -1 || hr > 24) // SMW believe it or not I found one with timestamp of 24
            {
                ts = ts.Substring(yrIdx,yrLth+moLth+dyLth+dotLth) + "00" + ts.Substring(mnIdx);
            }
            int mn = getMinute(ts);
            if (mn == -1 || mn > 59)
            {
                ts = ts.Substring(yrIdx,yrLth+moLth+dyLth+dotLth+hrLth) + "00" + ts.Substring(scIdx);
            }
            int sc = getSecond(ts);
            if (sc == -1 || sc > 59)
            {
                ts = ts.Substring(yrIdx,yrLth+moLth+dyLth+dotLth+hrLth+mnLth) + "00";
            }
            return ts;
        }

        public static string toUtcString(string vistaTS)
        {
            if (vistaTS == "")
            {
                return "";
            }
            if (vistaTS == "*SENSITIVE*")
            {
                return vistaTS;
            }
            string correctedTS = corrected(vistaTS);
            if (correctedTS == "")
            {
                return "";
            }
            int yr = getYear(correctedTS);
            string result = Convert.ToString(yr);
            result += correctedTS.Substring(moIdx);
            return result;
        }

        public static DateTime toDateTimeFromRdv(string rdvTS)
        {
            string[] parts = StringUtils.split(rdvTS, StringUtils.SPACE);
            string[] dateFlds = StringUtils.split(parts[0], StringUtils.SLASH);

            // TBD - should we do this? Technically '00' is an invalid value for the day and should be fixed at that VistA
            // sometimes we get '00' as the day value from VistA - we change this to '01' to create our DateTime object
            if (dateFlds[1].Equals("0") || dateFlds[1].Equals("00"))
            {
                dateFlds[1] = "01";
            }

            if (parts.Length == 1)
            {
                return new DateTime(Convert.ToInt16(dateFlds[2]),
                                    Convert.ToInt16(dateFlds[0]),
                                    Convert.ToInt16(dateFlds[1]));
            }
            else
            {
                string[] timeFlds = StringUtils.split(parts[1], StringUtils.COLON);
                try
                {
                    return new DateTime(Convert.ToInt16(dateFlds[2]),
                                        Convert.ToInt16(dateFlds[0]),
                                        Convert.ToInt16(dateFlds[1]),
                                        Convert.ToInt16(timeFlds[0]),
                                        Convert.ToInt16(timeFlds[1]),
                                        0);
                }
                catch (Exception e)
                {
                    if (timeFlds[0] == "24" && timeFlds[1] == "00")
                    {
                        timeFlds[0] = "00";
                        return new DateTime(Convert.ToInt16(dateFlds[2]),
                                            Convert.ToInt16(dateFlds[0]),
                                            Convert.ToInt16(dateFlds[1]),
                                            Convert.ToInt16(timeFlds[0]),
                                            Convert.ToInt16(timeFlds[1]),
                                            0);
                    }
                    throw;
                }
            }
        }

        /// <summary>
        /// Pad a month or day string with zeroes
        /// </summary>
        /// <param name="part">Month or day as string (e.g. "03" or "2" or "28" etc.)</param>
        /// <returns>Padded string using zero. Always returns a string of length 2</returns>
        private static string padDatePartWithZeroes(string part)
        {
            if (String.IsNullOrEmpty(part))
            {
                return "00";
            }
            part = part.Trim();
            if (part.Length == 1)
            {
                return "0" + part;
            }
            return part;
        }
        public static string toUtcFromRdv(string rdvTS)
        {
            if (String.IsNullOrEmpty(rdvTS))
            {
                return "";
            }
            string[] parts = StringUtils.split(rdvTS, StringUtils.SPACE);
            string[] dateFlds = StringUtils.split(parts[0], StringUtils.SLASH);

            string datePartyyyyMMdd = dateFlds[2] + padDatePartWithZeroes(dateFlds[0]) + padDatePartWithZeroes(dateFlds[1]);
            if (parts.Length == 1)
            {
                return datePartyyyyMMdd + ".000000";
            }
            else
            {
                string[] timeFlds = StringUtils.split(parts[1], StringUtils.COLON);
                return datePartyyyyMMdd + "." + timeFlds[0] + timeFlds[1] + "00"; 
                //return new DateTime(Convert.ToInt16(dateFlds[2]),
                //                    Convert.ToInt16(dateFlds[0]),
                //                    Convert.ToInt16(dateFlds[1]),
                //                    Convert.ToInt16(timeFlds[0]),
                //                    Convert.ToInt16(timeFlds[1]),
                //                    0);
            }
        }

        public static DateTime toDateTime(String vistaTS)
        {
    	    if (vistaTS.IndexOf("SENSITIVE") != -1)
    	    {
    		    return new DateTime();
    	    }
    	    if (vistaTS == "TODAY")
    	    {
    		    return DateTime.Now;
    	    }
    	    if (vistaTS == "")
    	    {
                return new DateTime();
    	    }
            try
            {
                string correctedTS = corrected(vistaTS);
                return new DateTime(getYear(correctedTS), getMonth(correctedTS), getDay(correctedTS),
                                    getHour(correctedTS), getMinute(correctedTS), getSecond(correctedTS));
            }
            catch (Exception)
            {
                // VISTA has some timestamps like NOW000.000000 - WTF are we supposed to do with these - they aren't valid timestamps anyways
                return new DateTime();
            }
        }

        /// <summary>Turns UTC string into VistA timestamp (year - 1700)
        /// </summary>
        /// <param name="ts"></param>
        /// <returns></returns>
        public static String fromUtcString(String ts)
        {
            //ts = corrected(ts);
            //ts = ts.Substring(0,ts.Length-3);
            int yr = Convert.ToInt32(ts.Substring(0,4));
            String result = Convert.ToString(yr - 1700);
            result += ts.Substring(4);
            return result;
        }

        /// <summary>Convert DateTime into VistA format
        /// </summary>
        /// <param name="t"></param>
        /// <returns>yyyMMdd.HHmmss with year - 1700</returns>
        public static String fromDateTime(DateTime t)
        {
            string sYear = Convert.ToString(t.Year - 1700);
            string sMonth = StringUtils.prependChars(Convert.ToString(t.Month),'0',2);
            string sDay = StringUtils.prependChars(Convert.ToString(t.Day),'0',2);
            String result = sYear + sMonth + sDay;
            result += '.';
            string sHour = StringUtils.prependChars(Convert.ToString(t.Hour), '0', 2);
            string sMins = StringUtils.prependChars(Convert.ToString(t.Minute), '0', 2);
            string sSecs = StringUtils.prependChars(Convert.ToString(t.Second), '0', 2);
            result += sHour + sMins + sSecs;
            return result;
        }

        /// <summary>
        /// Convert DateTime into VistA format
        /// </summary>
        /// <param name="t">DateTime</param>
        /// <returns>yyyMMdd with year - 1700</returns>
        public static String fromDateTimeShortString(DateTime t)
        {
            string sYear = Convert.ToString(t.Year - 1700);
            string sMonth = StringUtils.prependChars(Convert.ToString(t.Month), '0', 2);
            string sDay = StringUtils.prependChars(Convert.ToString(t.Day), '0', 2);
            return sYear + sMonth + sDay;
        }

        public static string noSeconds(string vistaTS)
        {
            if (vistaTS.IndexOf('.') == -1)
            {
                return vistaTS;
            }
            vistaTS = padTimeToSeconds(vistaTS);
            string[] flds = StringUtils.split(vistaTS, StringUtils.PERIOD);
            return flds[0] + '.' + flds[1].Substring(0, 4);
        }

	    public static string padTimeToSeconds(String vistaTS) 
	    {
            String result = vistaTS;
		    while (result.Length < 12)
		    {
			    result += '0';
		    }
		    return result;
	    }

        public static string fromUtcFromDate(string utcFrom)
        {
            if (utcFrom.IndexOf('.') == -1)
            {
                utcFrom += ".000000";
            }
            return fromUtcString(utcFrom);
        }

        public static string fromUtcToDate(string utcTo)
        {
            if (utcTo.IndexOf('.') == -1)
            {
                utcTo += ".235959";
            }
            return fromUtcString(utcTo);
        }

        public static string invertTimestamp(string ts)
        {
            Double dTs = Convert.ToDouble(ts);
            Double newTs = 9999999 - dTs;
            return Convert.ToString(newTs);
        }

        public static string Now
        {
            get { return fromDateTime(DateTime.Now); }
        }

        public static string trimZeroes(string ts)
        {
            int idx = ts.Length - 1;
            while (ts[idx] == '0')
            {
                idx--;
            }
            return ts.Substring(0, idx + 1);
        }
    }
}
