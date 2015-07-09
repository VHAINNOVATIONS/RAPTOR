using System;
using System.Collections.Generic;
using System.Text;
using System.Runtime.CompilerServices;
using System.Collections;
using System.Runtime.Serialization.Formatters.Binary;
using System.IO;

namespace gov.va.medora.utils
{
    public class StringUtils
    {
        public const string CRLF = "\r\n";
        public const string CARET = "^";
        public const string STICK = "|";
        public const string COLON = ":";
        public const string SEMICOLON = ";";
        public const string COMMA = ",";
        public const string PERIOD = ".";
        public const string SLASH = "/";
        public const string SPACE = " ";
        public const string EQUALS = "=";
        public const string AMPERSAND = "&";
        public const string ATSIGN = "@";
        public const string TILDE = "~";

        private static Random StringRandom = new Random();

        //[MethodImpl(MethodImplOptions.Synchronized)]
        public static string[] split(string s, char delimiter)
        {
            return split(s, Convert.ToString(delimiter));
        }

        //[MethodImpl(MethodImplOptions.Synchronized)]
        public static string[] split(string s, string delimiter)
        {
            return s.Split(new string[] { delimiter }, System.StringSplitOptions.None);
        }

        //[MethodImpl(MethodImplOptions.Synchronized)]
        public static string[] trimArray(string[] s)
        {
            int i;
            for (i = s.Length - 1; i >= 0; i--)
            {
                if (!String.IsNullOrEmpty(s[i]))
                {
                    break;
                }
            }

            string[] result = new string[i + 1];
            Array.Copy(s, result, i + 1);
            return result;
        }

        //[MethodImpl(MethodImplOptions.Synchronized)]
        public static bool isNumeric(string s)
        {
            if (String.IsNullOrEmpty(s))
            {
                return false;
            }
            for (int i = 0; i < s.Length; i++)
            {
                if (s[i] < '0' || s[i] > '9')
                {
                    return false;
                }
            }
            return true;
        }

        //[MethodImpl(MethodImplOptions.Synchronized)]
        public static bool isAlpha(string s)
        {
            if (s == "")
            {
                return false;
            }
            for (int i = 0; i < s.Length; i++)
            {
                if (!isAlphaChar(s[i]))
                {
                    return false;
                }
            }
            return true;
        }

        //[MethodImpl(MethodImplOptions.Synchronized)]
        public static bool isNumericChar(char c)
        {
            return (c >= '0' && c <= '9');
        }

        //[MethodImpl(MethodImplOptions.Synchronized)]
        public static bool isAlphaChar(char c)
        {
            return ((c >= 'A' && c <= 'Z') || (c >= 'a' && c <= 'z'));
        }

        //[MethodImpl(MethodImplOptions.Synchronized)]
        public static bool isAlphaNumericChar(char c)
        {
            return (isAlphaChar(c) || isNumericChar(c));
        }

        //[MethodImpl(MethodImplOptions.Synchronized)]
        public static bool isWhiteSpace(char c)
        {
            return (c == ' ' || c == '\t' || c == '\n' || c == '\r');
        }

        //[MethodImpl(MethodImplOptions.Synchronized)]
        public static string removeNonNumericChars(String s)
        {
            if (s == null)
            {
                return null;
            }
            StringBuilder rtn = new StringBuilder();
            for (int i = 0; i < s.Length; i++)
            {
                if ((s[i] >= '0' && s[i] <= '9') || s[i] == '.')
                {
                    rtn.Append(s[i]);
                }
            }
            return rtn.ToString();
        }

        //[MethodImpl(MethodImplOptions.Synchronized)]
        public static string piece(string s, string delimiter, int pieceNum)
        {
            string[] flds = split(s, delimiter);
            if (pieceNum > flds.Length)
            {
                return null;
            }
            return flds[pieceNum - 1];
        }

        //[MethodImpl(MethodImplOptions.Synchronized)]
        public static bool isEmpty(String s)
        {
            return s == null || s.Length == 0;
        }

        //[MethodImpl(MethodImplOptions.Synchronized)]
        public static string strPack(string s, int n)
        {
            int lth = s.Length;
            StringBuilder result = new StringBuilder(lth.ToString());
            while (result.Length < n)
            {
                result.Insert(0, "0");
            }
            return result + s;
        }

        //[MethodImpl(MethodImplOptions.Synchronized)]
        public static string varPack(string s)
        {
            if (String.IsNullOrEmpty(s))
            {
                s = "0";
            }
            StringBuilder b = new StringBuilder();
            b.Append('|');
            b.Append(Convert.ToChar(s.Length));
            b.Append(s);
            return b.ToString();
        }

        //[MethodImpl(MethodImplOptions.Synchronized)]
        public static string LPack(string s, int ndigits)
        {
            int lth = (String.IsNullOrEmpty(s) ? 0 : s.Length);
            string sLth = Convert.ToString(lth);
            int width = sLth.Length;
            if (ndigits < width)
            {
                throw new ArgumentException("Too few digits");
            }
            string result = "000000000" + Convert.ToString(lth);
            result = result.Substring(result.Length - ndigits) + s;
            return result;
        }

        //[MethodImpl(MethodImplOptions.Synchronized)]
        public static string SPack(string s)
        {
            int lth = s.Length;
            if (lth > 255)
            {
                throw new ArgumentException("Parameter exceeds 255 chars");
            }
            StringBuilder sb = new StringBuilder();
            sb.Append(Convert.ToChar(lth));
            sb.Append(s);
            return sb.ToString();
        }

        public static int getFirstWhiteSpaceAfter(string s, int idx)
        {
            int i = idx;
            while (i >= 0 && !isWhiteSpace(s[i]))
            {
                i--;
            }
            return i;
        }

        public static string filteredString(string s)
        {
            String result = "";
            for (int i = 0; i < s.Length; i++)
            {
                int c = asciiAt(s, i);
                if (c == 9)
                {
                    result += "        ";
                }
                else if ((c >= 32 && c <= 127) || (c >= 161 && c <= 255))
                {
                    result += s[i];
                }
                else if (c >= 128 && c <= 159)
                {
                    result += "?";
                }
                else if (c == 10 || c == 13 || c == 160)
                {
                    result += " ";
                }
            }
            return result;
        }

        public static int asciiAt(string s, int idx)
        {
            char c = s[idx];
            return (int)c;
        }

        public static string prependChars(string s, char c, int sLen)
        {
            string chars = new string(c, sLen);
            return chars.Substring(0, sLen - s.Length) + s;
        }

        public static int getIdx(string[] lines, string target, int startingIdx)
        {
            for (int i = startingIdx; i < lines.Length; i++)
            {
                if (lines[i].StartsWith(target))
                {
                    return i;
                }
            }
            return -1;
        }

        public static string trimTrailingZeroes(string s)
        {
            int i = s.Length - 1;
            while (s[i] == '0' && i > 0)
            {
                i--;
            }
            return s.Remove(i + 1);
        }

        public static string reverse(string s)
        {
            string result = "";
            for (int i = s.Length - 1; i >= 0; i--)
            {
                result += s[i];
            }
            return result;
        }

        /// <summary>
        /// Return a string with all invalid XML 1.0 characters 
        /// (per W3C standard: http://www.w3.org/TR/REC-xml/#charsets) removed
        /// </summary>
        /// <param name="s">String to filter</param>
        /// <returns>XML 1.0 compliant string</returns>
        public static string stripInvalidXmlCharacters(string s)
        {
            if (String.IsNullOrEmpty(s))
            {
                return "";
            }
            StringBuilder sb = new StringBuilder();
            for (int i = 0; i < s.Length; i++)
            {
                char current = s[i];
                if (current == 0x9 || current == 0xA || current == 0xD ||
                    ((current >= 0x20) && (current <= 0xD7FF)) ||
                    ((current >= 0xE000) && (current <= 0xFFFD)) ||
                    ((current >= 0x10000) && (current <= 0x10FFFF)))
                {
                    sb.Append(current);
                }
                else
                {
                    //System.Console.WriteLine("Found invalid XML: {0} - {1}", Convert.ToInt32(current), current);
                }
            }
            return sb.ToString();
        }

        /// <summary>
        /// Get a string of random characters (a-z, A-Z, 0-9) of length n
        /// </summary>
        /// <param name="n">The length of the random character string to generate</param>
        /// <returns>String of random characters of length n</returns>
        public static string getNCharRandom(int n)
        {
            char[] rndm = new char[n];
            char[] chars = new char[62];
            chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789".ToCharArray();

            for (int i = 0; i < n; i++)
            {
                rndm[i] = chars[StringRandom.Next(chars.Length)];
            }
            return new string(rndm);
        }

        /// <summary>
        /// Checks to see if a string is numeric and contains a decimal
        /// </summary>
        /// <param name="s">the string to evaluate</param>
        /// <returns>true if s isNumeric and contains a decimal, false otherwise</returns>
        public static bool isDecimal(string s)
        {
            return s.Contains(".") && isNumeric(s.Replace(".",""));
        }

        public static string stripNonPrintableChars(string s)
        {
            if (String.IsNullOrEmpty(s))
            {
                return "";
            }
            StringBuilder sb = new StringBuilder();
            for (int i = 0; i < s.Length; i++)
            {
                char current = s[i];
                if (current >= ' ' && current <= '~')
                {
                    sb.Append(current);
                }
            }
            return sb.ToString();
        }

        public static int firstIndexOfNum(string s)
        {
            if (String.IsNullOrEmpty(s))
                return -1;

            char[] nums = {'0','1','2','3','4','5','6','7','8','9'};
            return s.IndexOfAny(nums);
        }

        public static string trimCrLf(string s)
        {
            if (s.StartsWith("\r\n"))
            {
                s = s.Substring(2);
            }
            if (s.EndsWith("\r\n"))
            {
                s = s.Substring(0, s.Length - 2);
            }
            return s;
        }

        /// <summary>
        /// Convert a string in to a 32 character MD5 hash code
        /// </summary>
        /// <param name="input">ASCII string</param>
        /// <returns>MD5 hash of string</returns>
        public static string getMD5Hash(string input)
        {
            System.Security.Cryptography.MD5 md5 = System.Security.Cryptography.MD5.Create();
            byte[] hashStringBytes = md5.ComputeHash(System.Text.Encoding.ASCII.GetBytes(input));
            StringBuilder sb = new StringBuilder();
            foreach (byte b in hashStringBytes)
            {
                sb.Append(b.ToString("x2").ToUpper());
            }
            return sb.ToString();
        }

        /// <summary>
        /// Serialize an object and compute it's MDWS hash code
        /// </summary>
        /// <param name="input"></param>
        /// <returns></returns>
        public static string getMD5Hash(object input)
        {
            if (input is string)
            {
                return getMD5Hash((string)input);
            }

            BinaryFormatter bf = new BinaryFormatter();
            MemoryStream ms = new MemoryStream();
            bf.Serialize(ms, input);

            System.Security.Cryptography.MD5 md5 = System.Security.Cryptography.MD5.Create();
            byte[] hashBytes = md5.ComputeHash(ms.GetBuffer());
            StringBuilder sb = new StringBuilder();
            foreach (byte b in hashBytes)
            {
                sb.Append(b.ToString("x2").ToUpper());
            }
            return sb.ToString();
        }

        protected string getParameterizedCommandString(System.Data.IDbCommand command)
        {
            string outputText = "";

            if (command.Parameters.Count == 0)
            {
                outputText = command.CommandText;
            }
            else
            {
                StringBuilder output = new StringBuilder();
                output.Append(command.CommandText);
                output.Append("; ");

                System.Data.IDataParameter p;
                int count = command.Parameters.Count;
                for (int i = 0; i < count; i++)
                {
                    p = (System.Data.IDataParameter)command.Parameters[i];
                    output.Append(String.Format("{0} = '{1}'", p.ParameterName, p.Value));

                    if (i + 1 < count)
                    {
                        output.Append(", ");
                    }
                }
                outputText = output.ToString();
            }
            return outputText;
        }

        /// <summary>
        /// Give a string with a quoted string inside, obtain the first quoted string.
        /// e.g. extractQuoteString(@"This is a quoted string: "PEMDAS is the pnuemonic for order of operations"") 
        /// should return: "PEMDAS is the pnuemonic for order of operations"
        /// </summary>
        /// <param name="s"></param>
        /// <returns></returns>
        public static string extractQuotedString(string s)
        {
            if (String.IsNullOrEmpty(s) || !s.Contains("\""))
            {
                return "";
            }

            int firstQuote = s.IndexOf('"', 0);
            int secondQuote = s.IndexOf('"', firstQuote + 1);

            if (secondQuote <= 0)
            {
                return "";
            }
            return s.Substring(firstQuote + 1, secondQuote - firstQuote - 1);
        }

        /// <summary>
        /// Returns true if boolString is:
        /// 1) T (case insensitive)
        /// 2) TRUE (case insensitive)
        /// 3) 1
        /// 4) Y (case insensitive)
        /// 
        /// Otherwise returns false
        /// </summary>
        /// <param name="boolString"></param>
        /// <returns></returns>
        public static bool parseBool(String boolString)
        {
            return (String.Equals("T", boolString, StringComparison.CurrentCultureIgnoreCase) ||
                String.Equals("TRUE", boolString, StringComparison.CurrentCultureIgnoreCase) ||
                String.Equals("1", boolString, StringComparison.CurrentCultureIgnoreCase) ||
                String.Equals("Y", boolString, StringComparison.CurrentCultureIgnoreCase));
        }
    }
}
