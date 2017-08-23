using System;
using System.Collections.Generic;
using System.Text;
using gov.va.medora.utils;

namespace gov.va.medora.mdo
{
    public class PersonName
    {
        const String INVALID_NAME = "Invalid person name";

	    String lastname;
	    String firstname;
	    String inits;
	    String suffix;
	    String prefix;
	    String degree;
    	
	    public PersonName() {}
    	
	    public PersonName(String value)
	    {
		    if (!StringUtils.isEmpty(value))
		    {
			    splitName(value);
		    }
	    }

        public String Lastname
        {
            get
            {
                return lastname;
            }
            set
            {
                lastname = value;
            }
        }

        public String Firstname
        {
            get
            {
                return firstname;
            }
            set
            {
                firstname = value;
            }
        }

        public String Inits
        {
            get
            {
                return inits;
            }
            set
            {
                inits = value;
            }
        }

        public String Suffix
        {
            get
            {
                return suffix;
            }
            set
            {
                suffix = value;
            }
        }

        public String Prefix
        {
            get
            {
                return prefix;
            }
            set
            {
                prefix = value;
            }
        }

        public String Degree
        {
            get
            {
                return degree;
            }
            set
            {
                degree = value;
            }
        }

        private void splitName(String value)
	    {
            String[] names = StringUtils.split(value,StringUtils.COMMA);
            if (names.Length == 1)
            {
        	    Lastname = value;
                Firstname = "";
                return;
            }
            Lastname = names[0];
            Firstname = names[1].Trim();
        }

        public String LastNameFirst
        {
            get
            {
                return getLastNameFirst();
            }
            set
            {

                if (StringUtils.isEmpty(value))
                {
                    return;
                }
                splitName(value);
            }
        }

        public String getFirstNameFirst()
        {
    	    if (StringUtils.isEmpty(Lastname))
    	    {
    		    return "";
    	    }
            String s = Firstname + ' ';
            if (!StringUtils.isEmpty(Inits))
            {
        	    s += Inits + ' ';
            }
            s += Lastname;
            if (!StringUtils.isEmpty(Suffix))
            {
        	    s += Suffix + ' ';
            }
            return s;
        }

        public String getLastNameFirst()
        {
            if (StringUtils.isEmpty(Lastname))
            {
                return "";
            }
            StringBuilder name = new StringBuilder(Lastname);
            if (!StringUtils.isEmpty(Firstname))
            {
                name.Append(",");
                name.Append(Firstname);
            }
            if (!StringUtils.isEmpty(Inits))
            {
                name.Append(" ");
                name.Append(Inits);
            }
            if (!StringUtils.isEmpty(Suffix))
            {
                name.Append(Suffix);
                name.Append(" ");
            }
            return name.ToString();
        }	
        
        public String getFullName()
        {
    	    if (StringUtils.isEmpty(Lastname))
    	    {
    		    return "";
    	    }
    	    String s = "";
    	    if (!StringUtils.isEmpty(Prefix))
    	    {
    		    s += Prefix + ' ';
    	    }
    	    s += getFirstNameFirst();
    	    if (!StringUtils.isEmpty(Degree))
    	    {
    		    s += ", " + Degree;
    	    }
    	    return s;
        }

        public static bool isValid(string s)
        {
            if (StringUtils.isEmpty(s) || !StringUtils.isAlphaChar(s[0]))
            {
                return false;
            }
            for (int i = 1; i < s.Length; i++)
            {
                if (!StringUtils.isAlphaChar(s[i]) &&
                    s[i] != ' ' && s[i] != '\'' && s[i] != '-' && s[i] != ',' && s[i] != '.')
                {
                    return false;
                }
            }
            return true;
        }

        public override string ToString()
        {
            return getLastNameFirst();
        }
    }
}
