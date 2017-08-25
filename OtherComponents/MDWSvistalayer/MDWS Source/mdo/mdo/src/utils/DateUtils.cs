#define REFACTORING // support hhmmss in date conversion...
using System;
using System.Collections.Generic;
using System.Text;
using gov.va.medora.mdo.exceptions;

namespace gov.va.medora.utils
{
    /// <summary>
    /// Used to specify date format in string type date arguments
    /// </summary>
    public enum DateFormat
    {
        VISTA,
        ISO,
        UTC
    }

    public class DateUtils
    {
        public static string MinDate {
            get { return "18900101";  }
        }

        public static string Today {
            get { return DateTime.Now.ToString("yyyyMMdd.235959"); } 
        }

        public static bool isLeapYear(int year)
        {
            return ((year > 0) && (year % 4 == 0) && ((year % 100 != 0) || (year % 400 == 0)));
        }

        public static bool isValidMonth(int month)
        {
            return (month > 0 && month < 13);
        }

        public static bool isValidDay(int year, int month, int dy)
        {
            if (dy < 1 || dy > 31)
                return false;
            if (is30DayMonth(month) && dy > 30)
                return false;
            if (month != 2)
                return true;
            if (dy > 29)
                return false;
            if (!isLeapYear(year) && dy > 28)
                return false;
            return true;
        }

        public static bool is30DayMonth(int month)
        {
            return (month == 4 || month == 6 || month == 9 || month == 11);
        }

        public static bool isWellFormedDatePart(string dt)
        {
            if (String.IsNullOrEmpty(dt))
            {
                return false;
            }
            string d = "";
            int p = dt.IndexOf('.');
            if (p == -1)
            {
                d = dt;
            }
            else if (p != 8)
            {
                return false;
            }
            else
            {
                d = dt.Substring(0, 8);
            }
            if (d.Length != 8)
            {
                return false;
            }
            if (!StringUtils.isNumeric(d))
            {
                return false;
            }
            int yr = Convert.ToInt16(d.Substring(0, 4));
            if (yr < 1890)
            {
                return false;
            }
            int mo = Convert.ToInt16(d.Substring(4, 2));
            if (mo > 12)
            {
                return false;
            }
            int dy = Convert.ToInt16(d.Substring(6));
            if (dy > 31)
            {
                return false;
            }
            if ((mo == 4 || mo == 6 || mo == 9 || mo == 11) && dy > 30)
            {
                return false;
            }
            if (mo == 2 && dy > 29)
            {
                return false;
            }
            if (mo == 2 && !isLeapYear(yr) && dy > 28)
            {
                return false;
            }
            return true;
        }

        public static bool isWellFormedTimePart(string dt)
        {
            int p = dt.IndexOf('.');
            if (p == -1)
            {
                return true;
            }
            if (p != 8)
            {
                return false;
            }
            string t = dt.Substring(9);
            // Valid Vista dates do NOT need to have 6 time digits: 3110101.10 is a valid timestamp, for example
            //if (t.Length != 6)
            //{
            //    return false;
            //}
            if (!StringUtils.isNumeric(t))
            {
                return false;
            }
            if (t.Length >= 2)
            {
                int hr = Convert.ToInt16(t.Substring(0, 2));
                if (hr > 23)
                {
                    return false;
                }
            }
            if (t.Length >= 4)
            {
                int mn = Convert.ToInt16(t.Substring(2, 2));
                if (mn > 59)
                {
                    return false;
                }
            }
            if (t.Length >= 6)
            {
                int sc = Convert.ToInt16(t.Substring(4));
                if (sc > 59)
                {
                    return false;
                }
            }
            return true;
        }

        public static bool isWellFormedUtcDateTime(string dt)
        {
            if (isWellFormedDatePart(dt) && isWellFormedTimePart(dt))
            {
                return true;
            }
            return false;
        }

        /// <summary>
        /// Changes yyyyMMdd.HHmmss formatted string to date.
        /// </summary>
        /// <remarks>pads missing least significant digits (e.g. seconds or milliseconds)
        /// with 0s.</remarks>
        /// <param name="dateString"></param>
        /// <returns></returns>
        public static DateTime IsoDateStringToDateTime(string dateString)
        {
#if REFACTORING
            DateTime retTime =  new DateTime(int.Parse(dateString.Substring(0, 4))
                                            , int.Parse(dateString.Substring(4, 2))
                                            , int.Parse(dateString.Substring(6, 2)));
            double result = 0;
            if (dateString.Length < 18)
            {
                dateString = dateString.PadRight(18, '0');
            }
            if (double.TryParse(dateString.Substring(9, 2), out result))
            {
                retTime = retTime.AddHours(result);
            }
            if (double.TryParse(dateString.Substring(11, 2), out result))
            {
                retTime = retTime.AddMinutes(result);
            }
            if (double.TryParse(dateString.Substring(13, 2), out result))
            {
                retTime = retTime.AddSeconds(result);
            }
            if (double.TryParse(dateString.Substring(15, 3), out result))
            {
                retTime = retTime.AddMilliseconds(result);
            }

            return retTime;
#else
            return new DateTime(int.Parse(dateString.Substring(0, 4)), int.Parse(dateString.Substring(4, 2)), int.Parse(dateString.Substring(6, 2)));
#endif // REFACTORING
        }
        
        public static string trimSeconds(string timestamp)
        {
            int p = timestamp.IndexOf(".");
            if (p == -1)
            {
                return timestamp;
            }
            string[] parts = StringUtils.split(timestamp, StringUtils.PERIOD);
            if (parts[1].Length < 5)
            {
                return timestamp;
            }
            parts[1] = parts[1].Substring(0,4);
            return parts[0] + '.' + parts[1];
        }

        public static string zeroSeconds(string timestamp)
        {
            int p = timestamp.IndexOf(".");
            if (p == -1)
            {
                return timestamp + ".000000";
            }
            string[] parts = StringUtils.split(timestamp, StringUtils.PERIOD);
            if (parts[1].Length < 5)
            {
                return timestamp + "000000".Substring(0,6-parts[1].Length);
            }
            parts[1] = parts[1].Substring(0, 4) + "00";
            return parts[0] + '.' + parts[1];
        }

        /// <summary>Removes the time portion (if present) of a date/time string of expected format.
        /// </summary>
        /// <remarks>
        /// NOTE: this function doesn't check for well-formatted-ness of timestamp argument. If you need to
        /// check, use the <code>isWellFormed...()</code> functions
        /// </remarks>
        /// <param name="timestamp">yyyyMMdd.HHmmss or yyyyMMdd</param>
        /// <returns>the date portion of a date/time string</returns>
        public static string trimTime(string timestamp)
        {
            int p = timestamp.IndexOf(".");
            if (p == -1)
            {
                return timestamp;
            }
            string[] parts = StringUtils.split(timestamp, StringUtils.PERIOD);
            return parts[0];
        }

        public static void CheckDateRange(string fromDate, string toDate)
        {
            if (!isWellFormedUtcDateTime(fromDate))
            {
                throw new MdoException(MdoExceptionCode.ARGUMENT_DATE_FORMAT, "Invalid 'from' date: " + fromDate);
            }
            if (!isWellFormedUtcDateTime(toDate))
            {
                throw new MdoException(MdoExceptionCode.ARGUMENT_DATE_FORMAT, "Invalid 'to' date: " + toDate);
            }
            if (fromDate == toDate)
            {
                throw new InvalidDateRangeException();
            }

            string fromDatePart = "";
            string fromTimePart = "";
            int p = fromDate.IndexOf('.');
            if (p == -1)
            {
                fromDatePart = fromDate;
            }
            else
            {
                fromDatePart = fromDate.Substring(0, 8);
                fromTimePart = fromDate.Substring(9);
            }

            string toDatePart = "";
            string toTimePart = "";
            p = toDate.IndexOf('.');
            if (p == -1)
            {
                toDatePart = toDate;
            }
            else
            {
                toDatePart = toDate.Substring(0, 8);
                toTimePart = toDate.Substring(9);
            }
            if (String.CompareOrdinal(fromDatePart, toDatePart) > 0)
            {
                throw new InvalidDateRangeException();
            }
            if (fromDatePart == toDatePart && String.CompareOrdinal(fromTimePart,toTimePart) > 0)
            {
                throw new InvalidDateRangeException();
            }
        }

        string subtractSecond(string timestamp)
        {
            string[] parts = StringUtils.split(timestamp, StringUtils.PERIOD);

            // Only do this if there is a time part that ends with 0.
            if (parts.Length < 2 || !parts[1].EndsWith("0"))
            {
                return timestamp;
            }

            return "";
        }

        public static string toVistaTimestampString(DateTime dateTime)
        {
            return dateTime.ToString("yyyyMMdd.235959");
        }

        public static string trimTrailingTimeZeroes(String dateTime)
        {
            if (dateTime.IndexOf('.') < 0)
            {
                return dateTime;
            }

            String[] pieces = dateTime.Split(new char[] { '.' });
            String newTimePart = pieces[1].TrimEnd(new char[] { '0' });
            if (String.IsNullOrEmpty(newTimePart)) // midnight!
            {
                return pieces[0];
            }
            else 
            {
                return String.Concat(pieces[0], '.', newTimePart);
            }
        }
    }
}
