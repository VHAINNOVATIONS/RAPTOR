using System;
using System.Collections.Generic;
using System.Text;
using System.Text.RegularExpressions;

using gov.va.medora.utils;

namespace gov.va.medora.mdo
{
    /// <summary>
    /// 
    /// </summary>
    /// <remarks>
    /// Contributions by Matt Schmidt (vhaindschmim0) and Robert Ruff (vhawpbruffr)
    /// </remarks>
    public class SocSecNum
    {
        protected string myAreaNumber;
        protected string myGroupNumber;
        protected string mySerialNumber;

        const string IssuedByWoolworth = "078051120";
        const string SSAPamphlet = "219099999";
        protected const string INVALID_SSN = "Invalid SSN";

        bool _sensitive;
        const string SENSITIVE = "*SENSITIVE*";

        public SocSecNum() {}

        public SocSecNum(string value)
        {
            setSSN(value);
        }

        /// <summary>
        /// Use this constructor to create a SocSecNum object without an SSN string. Pass true as the argument
        /// and use the SocSecNum.SensitivityString accessor to obtain the sensitivity string
        /// </summary>
        /// <param name="sensitive">Pass true for sensitive SSNs</param>
        public SocSecNum(bool sensitive)
        {
            _sensitive = sensitive;
        }

        void setSSN(string value)
        {
            if (String.IsNullOrEmpty(value))
            {
                return;
                //throw new ArgumentException(INVALID_SSN);
            }
            if (value.ToUpper().Contains("SENSITIVE"))
            {
                _sensitive = true;
                return;
            }
            string myValue = StringUtils.removeNonNumericChars(value);
            if (myValue.Length != 9 || !StringUtils.isNumeric(myValue))
            {
                throw new ArgumentException(INVALID_SSN);
            }
            myAreaNumber = stripField(myValue, 1);
            myGroupNumber = stripField(myValue, 2);
            mySerialNumber = stripField(myValue, 3);
        }

        /// <summary>
        /// Returns the constant sensitivity string (*SENSITIVE*)
        /// </summary>
        public string SensitivityString
        {
            get { return SENSITIVE; }
        }

        /// <summary>
        /// Sensitive setting accessor
        /// </summary>
        public bool Sensitive
        {
            get { return _sensitive; }
            set { _sensitive = value; }
        }

        public string AreaNumber
        {
            get { return myAreaNumber; }
            set 
            {
                myAreaNumber = value;
                if (!IsValidAreaNumber)
                {
                    throw new ArgumentException("Invalid area number");
                }
            }
        }

        public string GroupNumber
        {
            get { return myGroupNumber; }
            set 
            { 
                myGroupNumber = value;
                if (!IsValidGroupNumber)
                {
                    throw new ArgumentException("Invalid group number");
                }
            }
        }

        public string SerialNumber
        {
            get { return mySerialNumber; }
            set 
            { 
                mySerialNumber = value;
                if (!IsValidSerialNumber)
                {
                    throw new ArgumentException("Invalid serial number");
                }
            }
        }

        internal string setIfNumeric(string s)
        {
            if (!StringUtils.isNumeric(s))
            {
                throw new ArgumentException(INVALID_SSN);
            }
            return s;
        }

        public bool IsWellFormedAreaNumber
        {
            get { return isWellFormedAreaNumber(myAreaNumber); }
        }

        public static bool isWellFormedAreaNumber(string value)
        {
            if (value.Length != 3 || !StringUtils.isNumeric(value))
            {
                return false;
            }
            return true;
        }

        public virtual bool IsValidAreaNumber
        {
            get { return isValidAreaNumber(myAreaNumber); }
        }

        public static bool isValidAreaNumber(string value)
        {
            if (!isWellFormedAreaNumber(value))
            {
                return false;
            }
            int iAreaNumber = Convert.ToInt16(value);
            if (iAreaNumber == 0 || 
                (iAreaNumber > 649 && iAreaNumber < 700) ||
                (iAreaNumber > 772))
            {
                return false;
            }
            return true;
        }

        public bool IsWellFormedGroupNumber
        {
            get { return isWellFormedGroupNumber(myGroupNumber); }
        }

        public static bool isWellFormedGroupNumber(string value)
        {
            if (value.Length != 2 || !StringUtils.isNumeric(value))
            {
                return false;
            }
            return true;
        }

        public bool IsValidGroupNumber
        {
            get { return isValidGroupNumber(myGroupNumber); }
        }

        public static bool isValidGroupNumber(string value)
        {
            if (!isWellFormedGroupNumber(value))
            {
                return false;
            }
            return (value != "00");
        }

        public bool IsWellFormedSerialNumber
        {
            get { return isWellFormedSerialNumber(mySerialNumber); }
        }

        public static bool isWellFormedSerialNumber(string value)
        {
            if (value.Length != 4 || !StringUtils.isNumeric(value))
            {
                return false;
            }
            return true;
        }

        public bool IsValidSerialNumber
        {
            get { return isValidSerialNumber(mySerialNumber); }
        }

        public static bool isValidSerialNumber(string value)
        {
            if (!isWellFormedSerialNumber(value))
            {
                return false;
            }
            return (value != "0000");
        }

        public bool IsWellFormed
        {
            get { return isWellFormed(toString()); }
        }

        public static bool isWellFormed(string value)
        {
            string myValue = StringUtils.removeNonNumericChars(value);
            if (myValue.Length != 9 || !StringUtils.isNumeric(myValue))
            {
                return false;
            }
            return isWellFormedAreaNumber(stripField(myValue, 1)) &&
                   isWellFormedGroupNumber(stripField(myValue, 2)) &&
                   isWellFormedSerialNumber(stripField(myValue, 3));
        }

        public bool IsValid
        {
            get { return isValid(toString()); }
        }

        public static bool isValid(string value)
        {
            if (!isWellFormed(value))
            {
                return false;
            }
            if (value == IssuedByWoolworth || value == SSAPamphlet)
            {
                return false;
            }
            return isValidAreaNumber(stripField(value, 1)) &&
                   isValidGroupNumber(stripField(value, 2)) &&
                   isValidSerialNumber(stripField(value, 3));
        }

        public static string stripField(string value, int fldnum)
        {
            string myValue = StringUtils.removeNonNumericChars(value);
            switch (fldnum)
            {
                case 1:
                    if (myValue.Length < 3)
                    {
                        return "";
                    }
                    return myValue.Substring(0, 3);
                case 2:
                    if (myValue.Length < 5)
                    {
                        return "";
                    }
                    return myValue.Substring(3, 2);
                case 3:
                    if (myValue.Length < 9)
                    {
                        return "";
                    }
                    return myValue.Substring(5,4);
                default:
                    return "";
            }
        }

        public override string  ToString()
        {
 	        return this.toString();
        }

        public virtual string toString()
        {
            if (_sensitive)
            {
                return SENSITIVE;
            }
            return myAreaNumber + myGroupNumber + mySerialNumber;
        }

        public virtual string toHyphenatedString()
        {
            if (_sensitive)
            {
                return SENSITIVE;
            }
            return myAreaNumber + '-' + myGroupNumber + '-' + mySerialNumber;
        }

    }
}
