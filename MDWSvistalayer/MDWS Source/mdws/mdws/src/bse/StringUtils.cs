using System;
using System.Collections.Generic;
using System.Text;
using System.Runtime.CompilerServices;
using System.Collections;

namespace gov.va.medora.mdws.bse
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
                if (s[i] != "")
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
            if (s == "")
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
            String rtn = "";
            for (int i = 0; i < s.Length; i++)
            {
                if (s[i] >= '0' && s[i] <= '9')
                {
                    rtn += s[i];
                }
            }
            return rtn;
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
        public static string strPack(String s, int n)
        {
            int lth = s.Length;
            String result = lth.ToString();
            while (result.Length < n)
            {
                result = '0' + result;
            }
            return result + s;
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
    }
}
